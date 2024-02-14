# post-install script for Jeedom plugin myToyota
PROGRESS_FILE=/tmp/jeedom_install_in_progress_myToyota
if [ -n "$1" ]; then
	PROGRESS_FILE="$1"
fi
if [ -d /var/www/html/plugins/myToyota ]; then
  cd /var/www/html/plugins/myToyota
else
  echo "Le plugin doit être installé pour que ce script puisse être appelé"
  exit
fi
TMP_FILE=/tmp/post-install_myToyota_bashrc
export PYENV_ROOT="$(realpath ressources)/_pyenv"
PYENV_VERSION="3.11.6"

touch "$PROGRESS_FILE"
echo 0 > "$PROGRESS_FILE"
echo "********************************************************"
echo "*           Nettoyage de l'ancienne version            *"
echo "********************************************************"
date
echo 5 > "$PROGRESS_FILE"
echo "********************************************************"
echo "*            Installation de pyenv                     *"
echo "********************************************************"
date
ldconfig
if [ -d "$PYENV_ROOT" ] && [ ! -d "$PYENV_ROOT/.git" ]; then
  rm -rf "$PYENV_ROOT"
fi
if [ ! -d "$PYENV_ROOT" ]; then
  sudo -E -u www-data curl https://pyenv.run | bash
  echo 20 > "$PROGRESS_FILE"
fi
echo "****  Configuration de pyenv..."
grep -vi pyenv ~/.bashrc > "$TMP_FILE"
cat "$TMP_FILE" > ~/.bashrc
cat >> ~/.bashrc<< EOF
export PYENV_ROOT="$PYENV_ROOT"
command -v pyenv >/dev/null || export PATH="\$PYENV_ROOT/bin:\$PATH"
eval "\$(pyenv init -)"
EOF
sudo -E -u www-data grep -vi pyenv ~www-data/.bashrc > "$TMP_FILE"
cat "$TMP_FILE" > ~www-data/.bashrc
sudo -E -u www-data cat >> ~www-data/.bashrc<< EOF
export PYENV_ROOT="$PYENV_ROOT"
command -v pyenv >/dev/null || export PATH="\$PYENV_ROOT/bin:\$PATH"
eval "\$(pyenv init -)"
EOF
echo "****  Suppression des anciennes versions de pyenv..."
for version in `"$PYENV_ROOT"/bin/pyenv versions --bare`; do
  if [ ! "$version" = "$PYENV_VERSION" ]; then
    "$PYENV_ROOT"/bin/pyenv uninstall -f "$version"
  fi
done
echo 30 > "$PROGRESS_FILE"
if [ ! -d "$PYENV_ROOT/versions/$PYENV_VERSION" ]; then
  echo "********************************************************"
  echo "*    Installation de python $PYENV_VERSION (dure longtemps)    *"
  echo "********************************************************"
  date
  echo "**** Mise à jour de pyenv"
  chown -R root:root "$PYENV_ROOT"
  cd "$PYENV_ROOT" && git reset --hard HEAD && git clean -fdx && git pull && cd -
  echo "**** Mise à jour de pyenv terminée, installation de python $PYENV_VERSION"
  chown -R www-data:www-data "$PYENV_ROOT"
  sudo -E -u www-data "$PYENV_ROOT"/bin/pyenv install "$PYENV_VERSION"
fi
echo 95 > "$PROGRESS_FILE"
echo "********************************************************"
echo "*      Configuration de pyenv avec python $PYENV_VERSION       *"
echo "********************************************************"
date
command -v pyenv >/dev/null || export PATH="$PYENV_ROOT/bin:$PATH"
eval "$(pyenv init -)"
cd ressources/prog_python
chown -R www-data:www-data "$PYENV_ROOT"
sudo -E -u www-data "$PYENV_ROOT"/bin/pyenv local "$PYENV_VERSION"
# sudo -E -u www-data "$PYENV_ROOT"/bin/pyenv exec pip install --upgrade pip setuptools
# chown -R www-data:www-data "$PYENV_ROOT"
# sudo -E -u www-data "$PYENV_ROOT"/bin/pyenv exec pip install --upgrade requests pyserial pyudev
# chown -R www-data:www-data "$PYENV_ROOT"
sudo -E -u www-data "$PYENV_ROOT"/bin/pyenv exec pip install mytoyota # ==1.3.1
chown -R www-data:www-data "$PYENV_ROOT"
echo 100 > "$PROGRESS_FILE"
rm "$PROGRESS_FILE"
rm "$TMP_FILE"
echo "********************************************************"
echo "*           Installation terminée                      *"
echo "********************************************************"
date