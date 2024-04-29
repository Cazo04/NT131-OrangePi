from flask import Flask, request, jsonify
import wiringpi
from wiringpi import GPIO
import threading
import time

app = Flask(__name__)

wiringpi.wiringPiSetup()

running = True

pins = [3, 4, 6, 9]
for pin in pins:
    wiringpi.pinMode(pin, GPIO.OUTPUT)

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


automode = True

@app.route('/setauto/<int:state>', methods=['GET'])
def set_auto(state):
    global automode 
    automode = True if state else False
    print("Auto mode:", "ON" if automode else "OFF")
    if state:
        return {"message": "Auto mode enabled"}, 200
    else:
        return {"message": "Auto mode disabled"}, 200

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
                    if current_value:
                        wiringpi.digitalWrite(3, GPIO.HIGH)
                        wiringpi.digitalWrite(4, GPIO.LOW)
                    else:
                        wiringpi.digitalWrite(3, GPIO.LOW)
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

