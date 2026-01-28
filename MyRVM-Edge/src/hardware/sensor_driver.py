import time
try:
    import RPi.GPIO as GPIO
except ImportError:
    try:
        import Jetson.GPIO as GPIO
    except ImportError:
        GPIO = None

from .base_driver import BaseDriver

class SensorDriver(BaseDriver):
    """
    Driver for sensors (Ultrasonic HC-SR04, IR Proximity, DHT22).
    """
    def __init__(self, name, pin_config, sensor_type="ultrasonic"):
        super().__init__(name)
        self.pins = pin_config
        self.sensor_type = sensor_type.lower()

    def initialize(self):
        if GPIO is None:
            return False
            
        GPIO.setmode(GPIO.BCM)
        if self.sensor_type == "ultrasonic":
            GPIO.setup(self.pins['trigger'], GPIO.OUT)
            GPIO.setup(self.pins['echo'], GPIO.IN)
            GPIO.output(self.pins['trigger'], GPIO.LOW)
        elif self.sensor_type == "proximity":
            GPIO.setup(self.pins['pin'], GPIO.IN)
            
        return super().initialize()

    def read(self):
        """Reads data from the sensor."""
        if not self.is_initialized:
            return None

        if self.sensor_type == "ultrasonic":
            # Pulse trigger
            GPIO.output(self.pins['trigger'], GPIO.HIGH)
            time.sleep(0.00001)
            GPIO.output(self.pins['trigger'], GPIO.LOW)

            start_time = time.time()
            stop_time = time.time()

            # Record echo start/stop
            timeout = time.time() + 0.1
            while GPIO.input(self.pins['echo']) == 0:
                start_time = time.time()
                if start_time > timeout: return None
                
            while GPIO.input(self.pins['echo']) == 1:
                stop_time = time.time()
                if stop_time > timeout: return None

            # Distance calculation (sound speed 34300 cm/s)
            elapsed = stop_time - start_time
            distance = (elapsed * 34300) / 2
            return round(distance, 2)

        elif self.sensor_type == "proximity":
            # Returns True if object detected (active level check)
            active_level = self.pins.get('active_level', 'LOW')
            val = GPIO.input(self.pins['pin'])
            return val == (GPIO.LOW if active_level == 'LOW' else GPIO.HIGH)

        return None
