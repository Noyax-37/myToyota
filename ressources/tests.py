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
    vehicule = {}
    eqpmt = {}
    print("Retrieving cars...")

    cars = await client.get_vehicles(metric=True)
    for car in cars:
        await car.update()
        print("Vin du vehicule trouve : " + car.vin)
        eqpmt['idequiment'] = 153
        vehicule['vin'] = vin
        if (car.vin) == vin:
            print("OK => Le vehicule est bien present")
            trouve = 1
            codebrand = car._vehicle_info.brand
            if codebrand == 'T':
                vehicule["brand"] = 'Toyota'
            else:
                vehicule["brand"] = 'voir avec Noyax pour ajouter le code'
            vehicule["model"] = car._vehicle_info.car_line_name
            vehicule["year"] = car._vehicle_info.manufactured_date
            if car._vehicle_info.extended_capabilities.electric_pulse:
                type = "Electrique"
            else:
                if car._vehicle_info.extended_capabilities.hybrid_pulse:
                    type = "Hybride"
                else:
                    if car._vehicle_info.extended_capabilities.drive_pulse:
                        type = "Essence"
                    else:
                        if car._vehicle_info.extended_capabilities.hydrogen_pulse:
                            type = "Hydrogene"
                        else:
                            type = 'Inconnu'

            vehicule["type"] = type
            vehicule["mileage"] = car.dashboard.odometer
            if car.lock_status.doors != None:
                vehicule["doorDriverFront"] = 'OPEN'
                vehicule["doorDriverRear"] = 'OPEN'
                vehicule["doorPassengerFront"] = 'OPEN'
                vehicule["doorPassengerRear"] = 'OPEN'
                vehicule["doorLockState"] = 'UNLOCKED'
                vehicule["allDoorsState"] = 'OPEN'
                if car.lock_status.doors.driver_seat.closed:
                    vehicule["doorDriverFront"] = 'CLOSED'
                elif car.lock_status.doors.driver_seat.closed==None:
                    vehicule["doorDriverFront"] = 'UNKNOW'
                if car.lock_status.doors.driver_rear_seat.closed:
                    vehicule["doorDriverRear"] = 'CLOSED'
                if car.lock_status.doors.passenger_seat.closed:
                    vehicule["doorPassengerFront"] = 'CLOSED'
                if car.lock_status.doors.passenger_rear_seat.closed:
                    vehicule["doorPassengerRear"] = 'CLOSED'
                if car.lock_status.doors.driver_seat.closed and car.lock_status.doors.driver_rear_seat.closed and car.lock_status.doors.passenger_seat.closed and car.lock_status.doors.passenger_rear_seat.closed:
                    vehicule["allDoorsState"] = 'CLOSED'
                if car.lock_status.doors.driver_seat.locked and car.lock_status.doors.driver_rear_seat.locked and car.lock_status.doors.passenger_seat.locked and car.lock_status.doors.passenger_rear_seat.locked:
                    vehicule["doorLockState"] = 'LOCKED'

            if car.lock_status.windows != None:
                if car.lock_status.windows.driver_seat.closed:
                    vehicule["windowDriverFront"] = 'CLOSED'
                else:
                    vehicule["windowDriverFront"] = 'OPEN'
                if car.lock_status.windows.driver_rear_seat.closed:
                    vehicule["windowDriverRear"] = 'CLOSED'
                else:
                    vehicule["windowDriverRear"] = 'OPEN'
                if car.lock_status.windows.passenger_seat.closed:
                    vehicule["windowPassengerFront"] = 'CLOSED'
                else:
                    vehicule["windowPassengerFront"] = 'OPEN'
                if car.lock_status.windows.passenger_rear_seat.closed:
                    vehicule["windowPassengerRear"] = 'CLOSED'
                else:
                    vehicule["windowPassengerRear"] = 'OPEN'
                if car.lock_status.windows.driver_seat.closed or car.lock_status.windows.driver_rear_seat.closed or car.lock_status.windows.passenger_seat.closed or car.lock_status.windows.passenger_rear_seat.closed:
                    vehicule["allWindowsState"] = 'CLOSED'
                else:
                    vehicule["allWindowsState"] = 'OPEN'

            if hasattr(car.lock_status.doors,'trunk'):
                vehicule['trunk_state'] = car.lock_status.doors.trunk





            print (vehicule)
            # urllib.request.urlretrieve(car._vehicle_info.image, "../data/" + vin + ".jpg")
            sys.exit()
        else:
            print("NOK => pas le bon vehicule")
    
    if trouve == 0:
        print("Erreur de VIN, aucun vehicule ne correspond, recommencez avec le bon VIN")
                


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

