# post-install script for Jeedom plugin myToyota
#!/bin/bash
PROGRESS_FILE=/tmp/jeedom_install_in_progress_myToyota
if [ ! -z $1 ]; then
    PROGRESS_FILE=$1
fi
date
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "*************************************************"
echo "*  Installation des dépendances post-install.sh *"
echo "*************************************************"
BASEDIR=/var/www/html/plugins/myToyota/ressources
date

echo 5 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/venv" ];then
echo "Le dossier venv existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/venv
fi

echo 10 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/mytoyota" ];then
echo "Le dossier python mytoyota existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/mytoyota
fi

echo 15 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/.cache" ];then
echo "Le dossier .cache existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/.cache
fi

echo 20 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/JsonPath-PHPP" ];then
echo "Le dossier JsonPath-PHPP existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/JsonPath-PHPP
fi

echo 25 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/jeedom" ];then
echo "Le dossier python jeedom existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/jeedom
fi

echo 30 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/prog_python" ];then
echo "Le dossier prog_python  existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/prog_python
fi

echo 35 > ${PROGRESS_FILE}
if [ -d "$BASEDIR/tests" ];then
echo "Le dossier test existe, il sera supprimé !";
sudo -u www-data rm -r -f $BASEDIR/tests
fi

echo 40 > ${PROGRESS_FILE}
if [ -f "$BASEDIR/data.py" ];then
echo "Divers fichiers python existent, il seront supprimé !";
sudo -u www-data rm -f $BASEDIR/data.py
sudo -u www-data rm -f $BASEDIR/'Fichier a modifier.txt'
sudo -u www-data rm -f $BASEDIR/gps_kml.py
sudo -u www-data rm -f $BASEDIR/myToyotad.py
sudo -u www-data rm -f $BASEDIR/README.md
sudo -u www-data rm -f $BASEDIR/simple_client_example.py
sudo -u www-data rm -f $BASEDIR/synchro.py
sudo -u www-data rm -f $BASEDIR/tests.py
fi

date
rm ${PROGRESS_FILE}
echo "*************************************"
echo "*  Installation des dépendances OK  *"
echo "*************************************"