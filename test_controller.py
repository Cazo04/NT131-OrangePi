import time
import wiringpi
from wiringpi import GPIO

wiringpi.wiringPiSetup()

wiringpi.pinMode(26, GPIO.INPUT)

while True:
    value = wiringpi.digitalRead(26)
    print(value)
    wiringpi.delay(1000)