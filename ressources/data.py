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
        trouve = 1
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print("Vin du vehicule trouve: " + car.vin)
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Location: {car.location}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Lock Status: {car.lock_status}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Notifications: {[[x] for x in car.notifications]}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"vehicle infos: {car._vehicle_info}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Summary jour: {[[x] for x in await car.get_summary(date.today() - timedelta(days=7), date.today(), summary_type=SummaryType.DAILY)]}")  # noqa: E501 # pylint: disable=C0301
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Summary semaine: {[[x] for x in await car.get_summary(date.today() - timedelta(days=7 * 4), date.today(), summary_type=SummaryType.WEEKLY)]}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Summary mois: {[[x] for x in await car.get_summary(date.today() - timedelta(days=6 * 30), date.today(), summary_type=SummaryType.MONTHLY)]}" )
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Summary année: {[[x] for x in await car.get_summary(date.today() - timedelta(days=365), date.today(), summary_type=SummaryType.YEARLY)]}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        #print(f"Trips: f{await car.get_trips(date.today() - timedelta(days=7), date.today(), full_route=True)}")
        #print(f"Huile: {car._vehicle_info.}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"dump all: {car._dump_all()}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        print(f"Dashboard: {car.dashboard}")
        print("--------------------------------------------------------------------------------------------------------------------------------------------------------------------------")
        # urllib.request.urlretrieve(car._vehicle_info.image, "../data/" + vin + ".jpg")
        # sys.exit()
    
    if trouve == 0:
        print("Erreur, aucun vehicule trouve, probleme de connexion?")
                


parser = argparse.ArgumentParser(description='myToyota python for Jeedom plugin')
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--username", help="Log Level for the daemon", type=str)
parser.add_argument("--password", help="Log Level for the daemon", type=str)
parser.add_argument("--vin", help="Log Level for the daemon", type=str)
args = parser.parse_args()

log_level = args.loglevel
username = args.username
password = args.password
vin = args.vin

log = logging.getLogger()
for hdlr in log.handlers[:]:
    log.removeHandler(hdlr)
jeedom_utils.set_log_level(log_level)
                    
print('myToyota ------ all data : ')
client = MyT(username=username, password=password)
loop = asyncio.get_event_loop()
loop.run_until_complete(get_information())
loop.close()
sys.exit()

