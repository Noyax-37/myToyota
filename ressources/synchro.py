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
                        type = "Essence"
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
            urllib.request.urlretrieve(car._vehicle_info.image, "/var/www/html/plugins/myToyota/data/" + vin + ".png")
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

