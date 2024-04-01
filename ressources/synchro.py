#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4
"""recuperation des donnees de base"""
import asyncio
import urllib.request
from datetime import date, timedelta

from mytoyota.client import MyT
from mytoyota.models.summary import SummaryType
import sys
import argparse
try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)



async def get_information():
    trouve = 0
    """Test login and output from endpoints."""
    print("Logging in...")
    await client.login()

    print("Retrieving cars...")

    cars = await client.get_vehicles(metric=True)
    for car in cars:
        await car.update()
        print("Vin du vehicule trouve : " + car.vin)
        if (car.vin) == vin:
            print("OK => Vehicule trouve")
            trouve = 1
            if car._vehicle_info.extended_capabilities.electric_pulse:
                type = "Electrique"
            else:
                if car._vehicle_info.extended_capabilities.hybrid_pulse:
                    type = "Hybride"
                else:
                    if car._vehicle_info.extended_capabilities.drive_pulse:
                        type = "Thermique"
                    else:
                        type = 'Inconnu'
            print(str(car._vehicle_info.manufactured_date))
            if (str(car._vehicle_info.manufactured_date) == 'None'):
                dateutil = 'Inconnue'
            else:
                dateutil = str(car._vehicle_info.manufactured_date.strftime('%d-%m-%Y'))
            print("Type " + type)
            print("Date " + dateutil)
            print("Nom  " + car._vehicle_info.car_line_name)
            print("img  " + car._vehicle_info.image)

            print("Capa " + str(car._vehicle_info.extended_capabilities))
            car.type
            
            urllib.request.urlretrieve(car._vehicle_info.image, "/var/www/html/plugins/myToyota/data/" + vin + ".png")
            sys.exit()
        else:
            print("NOK => pas le bon vehicule")
    
    if trouve == 0:
        print("Erreur de VIN, aucun vehicule ne correspond, recommencez avec le bon VIN")
                

# extended_capabilities=_ExtendedCapabilitiesModel(c_scheduling=False, battery_status=False, bonnet_status=False, bump_collisions=False, buzzer_capable=False, charge_management=False, climate_capable=False, 
# climate_temperature_control_full=False, climate_temperature_control_limited=False, dashboard_warning_lights=False, door_lock_unlock_capable=False, drive_pulse=False, ecare=False, econnect_climate_capable=False, 
# econnect_vehicle_status_capable=False, electric_pulse=False, emergency_assist=False, enhanced_security_system_capable=False, equipped_with_alarm=False, ev_battery=False, ev_charge_stations_capable=False, 
# fcv_stations_capable=False, front_defogger=False, front_driver_door_lock_status=False, front_driver_door_open_status=False, front_driver_door_window_status=False, front_driver_seat_heater=False, 
# front_driver_seat_ventilation=False, front_passenger_door_lock_status=False, front_passenger_door_open_status=False, front_passenger_door_window_status=False, front_passenger_seat_heater=False, 
# front_passenger_seat_ventilation=False, fuel_level_available=True, fuel_range_available=False, guest_driver=False, hazard_capable=False, horn_capable=False, hybrid_pulse=True, hydrogen_pulse=False, 
# last_parked_capable=False, light_status=False, lights_capable=False, manual_rear_windows=False, mirror_heater=False, moonroof=False, next_charge=False, power_tailgate_capable=False, power_windows_capable=False, 
# rear_defogger=False, rear_driver_door_lock_status=False, rear_driver_door_open_status=False, rear_driver_door_window_status=False, rear_driver_seat_heater=False, rear_driver_seat_ventilation=False, rear_hatch_rear_window=False, 
# rear_passenger_door_lock_status=False, rear_passenger_door_open_status=False, rear_passenger_door_window_status=False, rear_passenger_seat_heater=False, rear_passenger_seat_ventilation=False, remote_econnect_capable=False, 
# remote_engine_start_stop=False, smart_key_status=False, steering_heater=False, stellantis_climate_capable=False, stellantis_vehicle_status_capable=False, sunroof=False, telemetry_capable=True, trunk_lock_unlock_capable=False, 
# try_and_play=False, vehicle_diagnostic_capable=True, vehicle_finder=False, vehicle_status=False, we_hybrid_capable=False, weekly_charge=False)


parser = argparse.ArgumentParser(description='myToyota python for Jeedom plugin')
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--username", help="Log Level for the daemon", type=str)
parser.add_argument("--password", help="Log Level for the daemon", type=str)
parser.add_argument("--vin", help="Log Level for the daemon", type=str)
args = parser.parse_args()

log_level = args.loglevel
USERNAME = args.username
PASSWORD = args.password
vin = args.vin

log = logging.getLogger()
for hdlr in log.handlers[:]:
    log.removeHandler(hdlr)
jeedom_utils.set_log_level(log_level)
                    
print('myToyota ------ synchro : ')

client = MyT(username=USERNAME, password=PASSWORD)
loop = asyncio.get_event_loop()
loop.run_until_complete(get_information())
loop.close()
sys.exit()

