from flask import Flask, request, jsonify
import wiringpi
from wiringpi import GPIO
import threading
import time
import mysql.connector
from mysql.connector import Error
import datetime


# Database area

def create_connection(host_name, port, user_name, user_password, db_name):
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password,
            database=db_name,
            port=port
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")

    return connection

connection = create_connection("localhost", 3306, "laravel", "mypassword", "laravel")

def execute_query(connection, query):
    cursor = connection.cursor()
    try:
        cursor.execute(query)
        connection.commit()
        print("Query executed successfully")
    except Error as e:
        print(f"The error '{e}' occurred")

def execute_read_query(connection, query):
    cursor = connection.cursor()
    result = None
    try:
        cursor.execute(query)
        result = cursor.fetchall()
        return result
    except Error as e:
        print(f"The error '{e}' occurred")

def get_system_status(connection):
    select_system_status_query = "SELECT * FROM system_statuses"
    system_status = execute_read_query(connection, select_system_status_query)

    if system_status:
        status = system_status[0]       
        return status
    else:
        print("No status found")
        return None

system_status = get_system_status(connection)

sleep_time = system_status[1].time() if system_status is not None else datetime.time(21, 00)
automode = True if system_status is not None and system_status[2] else False

print(f"Sleep Mode Time: {sleep_time}, Automode: {automode}")

# End database area 

app = Flask(__name__)

wiringpi.wiringPiSetup()

running = True

pins = [3, 4, 6, 9]
for pin in pins:
    wiringpi.pinMode(pin, GPIO.OUTPUT)
    wiringpi.digitalWrite(pin, GPIO.HIGH)

@app.route('/control/<int:pin>/<int:state>', methods=['GET'])
def control_pin(pin, state):
    if pin in pins:
        wiringpi.digitalWrite(pin, state)
        return {"message": f"GPIO {pin} set to {'LOW' if state else 'HIGH'}"}, 200
    else:
        return {"message": "Invalid GPIO pin"}, 400
    
@app.route('/status/delays', methods=['GET'])
def get_status():
    status = {}
    for pin in pins:
        status[f"gpio{pin}"] = "HIGH" if wiringpi.digitalRead(pin) else "LOW"

    global automode
    status["automode"] = automode
    status["light"] = read_light_value()
    status["human"] = read_human_value()
    return jsonify(status), 200

@app.route('/setauto/<int:state>', methods=['GET'])
def set_auto(state):
    global automode 
    automode = True if state else False
    print("Auto mode:", "ON" if automode else "OFF")
    if state:
        return {"message": "Auto mode enabled"}, 200
    else:
        return {"message": "Auto mode disabled"}, 200
    
@app.route('/setsleeptime/<string:time>', methods=['GET'])
def set_sleeptime(time):
    global sleep_time
    sleep_time = datetime.datetime.strptime(time, "%H:%M").time()
    print("Sleep time:", sleep_time)
    return {"message": "Sleep time:" + sleep_time.strftime("%H:%M")}, 200

light_value = None
light_value_lock = threading.Lock()
humman_value = None
humman_value_lock = threading.Lock()

def light_gpio():
    light_pin = 27
    wiringpi.pinMode(light_pin, GPIO.INPUT)
    humman_pin = 26
    wiringpi.pinMode(humman_pin, GPIO.INPUT)

    global light_value
    global automode
    global running
    global humman_value
    global sleep_time

    last_value = None
    while running:
        current_human_value = wiringpi.digitalRead(humman_pin)
        with humman_value_lock:
            humman_value = current_human_value
        current_value = wiringpi.digitalRead(light_pin)
        if current_human_value:
            if current_value != last_value:
                with light_value_lock:
                    light_value = current_value
                if automode:
                    print(sleep_time)
                    if datetime.datetime.now().time() <= sleep_time:
                        wiringpi.digitalWrite(4, GPIO.HIGH)
                        if current_value:
                            wiringpi.digitalWrite(3, GPIO.LOW)
                        else:
                            wiringpi.digitalWrite(3, GPIO.HIGH)
                    else:
                        wiringpi.digitalWrite(3, GPIO.HIGH)
                        if current_value:
                            wiringpi.digitalWrite(4, GPIO.LOW)
                        else:
                            wiringpi.digitalWrite(4, GPIO.HIGH)
                print("Giá trị đọc được thay đổi:", current_value)
                last_value = current_value
        else:
            wiringpi.digitalWrite(4, GPIO.HIGH)
            wiringpi.digitalWrite(3, GPIO.HIGH)
            last_value = -1
        wiringpi.delay(1000)

def read_light_value():
    with light_value_lock:
        return light_value
    
def read_human_value():
    with humman_value_lock:
        return humman_value
    

if __name__ == '__main__':
    gpio_thread = threading.Thread(target=light_gpio)
    gpio_thread.start()
    app.run(host='127.0.0.1', port=5000)

    try:
        while running:
            time.sleep(1)
    except KeyboardInterrupt:
        print("Stopping, please wait...")
        running = False
        gpio_thread.join()

