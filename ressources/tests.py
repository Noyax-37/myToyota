#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4
"""recuperation des donnees de base"""
import asyncio
import urllib.request
from datetime import date, timedelta
import simplekml


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
        coords1 = []
        lin = ''
        if (car.vin) == vin:
            print("OK => Le vehicule est bien present")
            trouve = 1
            i = 0
            jour1 = await car.get_trips(date.today()  - timedelta(days=7), date.today(), full_route=True)
            kml = simplekml.Kml()
            for route1 in jour1:
                lin = ''
                test = route1.route
                i += 1
                coords1.clear()
                for gps in test:
                    coords1 += [(float((gps[1])),float((gps[0])))]
                lin = kml.newlinestring(name=str(route1.start_time) + str(i), description="trajet " + str(i), coords = coords1)
                lin.style.linestyle.width = 5
                lin.style.linestyle.color = simplekml.Color.blue

                kml.save("/var/www/html/plugins/myToyota/data/trajets.kml")


            #print (f"Trips: f{jour1}")
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

