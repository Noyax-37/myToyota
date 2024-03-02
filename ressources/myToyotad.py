#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4
"""recuperation des donnees de base"""
import asyncio
import urllib.request
from datetime import date, timedelta, timezone

from mytoyota.client import MyT
from mytoyota.models.summary import SummaryType
import sys
import json
import argparse
try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)

def utc_to_local(utc_dt):
    return utc_dt.replace(tzinfo=timezone.utc).astimezone(tz=None)

async def get_information():
    pid = str(os.getpid())
    trouve = 0
    """Test login and output from endpoints."""
    logging.info("Logging in...")
    await client.login()
    vehicule = {}
    eqpmt = {}
    logging.info("Retrieving cars...")
    vehicule["doorDriverFront"] = 'UNKNOWN'
    vehicule["doorDriverRear"] = 'UNKNOWN'
    vehicule["doorPassengerFront"] = 'UNKNOWN'
    vehicule["doorPassengerRear"] = 'UNKNOWN'
    vehicule["allDoorsState"] = 'UNKNOWN'
    vehicule["doorLockState"] = 'UNKNOWN'
    vehicule["windowDriverFront"] = 'UNKNOWN'
    vehicule["windowDriverRear"] = 'UNKNOWN'
    vehicule["windowPassengerFront"] = 'UNKNOWN'
    vehicule["windowPassengerRear"] = 'UNKNOWN'
    vehicule["allWindowsState"] = 'UNKNOWN'
    vehicule['trunk_state'] = 'UNKNOW'
    vehicule['hood_state'] = 'UNKNOW'

    cars = await client.get_vehicles(metric=True)
    moyennes= ''
    for car in cars:
        await car.update()
        logging.info("Vin du vehicule trouve : " + car.vin)
        eqpmt['device'] = idvehicule
        vehicule['vin'] = vin
        if (car.vin) == vin:
            logging.info("OK => Le vehicule est bien present => " + nomvehicule)
            trouve = 1
            codebrand = car._vehicle_info.brand
            if codebrand == 'T':
                vehicule["brand"] = 'Toyota'
            else:
                vehicule["brand"] = 'voir avec Noyax pour ajouter le code'
            vehicule["model"] = car._vehicle_info.car_line_name
            vehicule["year"] = str(car._vehicle_info.manufactured_date.strftime('%d-%m-%Y'))
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
            
            if type == "Hybride" or type == "Thermique":
                if car._vehicle_info.fuel_type == 'B':
                    vehicule['carburant'] = 'Essence'
                else:
                    vehicule['carburant'] = 'Diesel'

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
                if car.lock_status.doors.trunk.closed:
                    vehicule['trunk_state'] = 'CLOSED'
                else:
                    vehicule['trunk_state'] == 'OPEN'
            if hasattr(car.lock_status.hood, 'closed'):
                if car.lock_status.hood.closed:
                    vehicule['hood_state'] = 'CLOSED'
                else:
                    vehicule['hood_state'] = 'OPEN'

            vehicule['moonroof_state'] = 'UNKNOWN'

            vehicule['tireFrontLeft_pressure'] = 0
            vehicule['tireFrontLeft_target'] = 0
            vehicule['tireFrontRight_pressure'] = 0
            vehicule['tireFrontRight_target'] = 0
            vehicule['tireRearLeft_pressure'] = 0
            vehicule['tireRearLeft_target'] = 0
            vehicule['tireRearRight_pressure'] = 0
            vehicule['tireRearRight_target'] = 0

            if hasattr(car.dashboard,'charging_status'):
                vehicule['chargingStatus'] = car.dashboard.charging_status
            vehicule['connectorStatus'] = False
            if hasattr(car.dashboard,'battery_range'):
                vehicule['beRemainingRangeElectric'] = car.dashboard.battery_range
            vehicule['chargingLevelHv'] = 'UNKNOWN'
            if hasattr(car.dashboard,'range'):
                vehicule['beRemainingRangeTotal'] = car.dashboard.range
            if hasattr(car.dashboard,'remaining_charge_time'):
                vehicule['chargingEndTime'] = car.dashboard.remaining_charge_time

            if car._vehicle_info.extended_capabilities.fuel_range_available:
                vehicule['beRemainingRangeFuelKm'] = car.dashboard.fuel_range
            if car._vehicle_info.extended_capabilities.fuel_level_available:
                vehicule['remaining_fuel'] = car.dashboard.fuel_level

            if hasattr(car.notifications,'message'):
                vehicule['vehicleMessages'] = '"' + str(car.notifications[0].message).replace(u'\xa0', u' ') + '"'
            vehicule['gps_coordinates'] = ''

            if hasattr(car.location.timestamp , 'strftime'):
                vehicule['lastUpdate'] = str(utc_to_local(car.location.timestamp).strftime('%d-%m-%Y à %H:%M:%S'))
                vehicule['gps_coordinates'] = str(car.location.latitude) + ',' + str(car.location.longitude)


            vehicule['totalEnergyCharged'] = 'UNKNOW'
            vehicule['chargingSessions'] = 'UNKNOW'
            if car.service_history != None:
                vehicule['services'] = '"' + str(car.service_history) + '"'
            moy_sem = dict()
            for moyennes in await car.get_summary(date.today() - timedelta(days=7), date.today(), summary_type=SummaryType.YEARLY):
                moy_sem = {'conso_moy':moyennes.average_fuel_consumed,'vit_moy':moyennes.average_speed,
                    'distance_tot':moyennes.distance,'duree_tot':str(moyennes.duration),
                    'distance_ev':moyennes.ev_distance,'duree_ev':str(moyennes.ev_duration),
                    'conso_essence':moyennes.fuel_consumed}
            vehicule['moy_sem'] = json.dumps(moy_sem)
            i = 1
            mestrajet = dict()
            for trajets in await car.get_trips(date.today() - timedelta(days=7), date.today(), full_route=False):
                mestrajet[str('trajet' + str(i))] = {'debut_trajet': str(utc_to_local(trajets.start_time).strftime('%d-%m-%Y %H:%M:%S')),
                    'conso_moy':trajets.average_fuel_consumed,
                    'distance_tot':trajets.distance,'duree_tot':str(trajets.duration),
                    'distance_ev':trajets.ev_distance,'duree_ev':str(trajets.ev_duration),
                    'conso_essence':trajets.fuel_consumed}
                i+=1
            vehicule['trajets'] = json.dumps(mestrajet)
            



#    $this->createCmd('climateNow_status', 'Statut climatiser', 49, 'info', 'string');
#    $this->createCmd('stopClimateNow_status', 'Statut stop climatiser', 50, 'info', 'string');
#    $this->createCmd('chargeNow_status', 'Statut charger', 51, 'info', 'string');
#    $this->createCmd('stopChargeNow_status', 'Statut stop charger', 52, 'info', 'string');
#    $this->createCmd('doorLock_status', 'Statut verrouiller', 53, 'info', 'string');
#    $this->createCmd('doorUnlock_status', 'Statut déverrouiller', 54, 'info', 'string');
#    $this->createCmd('lightFlash_status', 'Statut appel de phares', 55, 'info', 'string');
#    $this->createCmd('hornBlow_status', 'Statut klaxonner', 56, 'info', 'string');
#    $this->createCmd('vehicleFinder_status', 'Statut recherche véhicule', 57, 'info', 'string');
#    $this->createCmd('sendPOI_status', 'Statut envoi POI', 58, 'info', 'string');

#    $this->createCmd('presence', 'Présence domicile', 59, 'info', 'binary');
#    $this->createCmd('distance', 'Distance domicile', 60, 'info', 'numeric');


            vehicule['PID'] = str(pid)
            logging.debug (vehicule)
            try:
                JEEDOM_COM.add_changes('device::' + idvehicule, vehicule)
            except Exception:
                error_com = "Connexion error"
                logging.error(error_com)
            sys.exit()
        else:
            logging.info("NOK => pas le bon vehicule")
    
    if trouve == 0:
        logging.info("Erreur de VIN, aucun vehicule ne correspond, recommencez avec le bon VIN")
                


parser = argparse.ArgumentParser(description='myToyota python for Jeedom plugin')
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--nomvehicule", help="Nom du vehicule", type=str)
parser.add_argument("--idvehicule", help="Id de l equipement", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--username", help="Log Level for the daemon", type=str)
parser.add_argument("--password", help="Log Level for the daemon", type=str)
parser.add_argument("--vin", help="Log Level for the daemon", type=str)
args = parser.parse_args()

APIKEY = args.apikey
log_level = args.loglevel
CALLBACK = args.callback
idvehicule = args.idvehicule
nomvehicule = args.nomvehicule
USERNAME = args.username
PASSWORD = args.password
vin = args.vin

log = logging.getLogger()
for hdlr in log.handlers[:]:
    log.removeHandler(hdlr)
jeedom_utils.set_log_level(log_level)

JEEDOM_COM = jeedom_com(apikey=APIKEY, url=CALLBACK, cycle=10)
logging.info('myToyota ------ debut recup donnees du vehicule : ' + str(idvehicule))

logging.info('myToyota------ Apikey : ' + str(APIKEY))
logging.info('myToyota------ Log level : ' + str(log_level))
logging.info('myToyota------ Callback : ' + str(CALLBACK))
logging.info('myToyota------ Nom du vehicule : ' + str(nomvehicule))
logging.info('myToyota------ VIN du vehicule : ' + '*******') # + str(vin))
logging.info("myToyota------ ID de l'equipement : " + str(idvehicule))
logging.info("myToyota------ Nom du compte : " + '*******') #USERNAME)
logging.info('myToyota------ Passord du compte : ' + '*******') #PASSWORD) 

if not JEEDOM_COM.test():
    logging.error('MODEM------ Network communication issues. Please fix your Jeedom network configuration.')
    sys.exit()

                    
logging.info('myToyota ------ Interrogation du serveur : ')

client = MyT(username=USERNAME, password=PASSWORD)
loop = asyncio.get_event_loop()
loop.run_until_complete(get_information())
loop.close()
sys.exit()

