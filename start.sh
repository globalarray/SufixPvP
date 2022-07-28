#!/bin/bash
DIR="$(cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd)"
cd "$DIR"

while getopts "p:f:l" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		f)
			POCKETMINE_FILE="$OPTARG"
			;;
		l)
			DO_LOOP="yes"
			;;
		\?)
			break
			;;
	esac
done

DO_LOOP="yes"
PHP_BINARY="./bin/php7/bin/php"
POCKETMINE_FILE="./src/pocketmine/SufixBase.php"
LOOPS=0

set +e
if [ "$DO_LOOP" == "yes" ]; then
	while true; do
		if [ ${LOOPS} -gt 0 ]; then
			echo "[Info] Сервер перезагрузился автоматически $LOOPS раз(а)."
		fi
		"$PHP_BINARY" "$POCKETMINE_FILE" $@
		echo "[Info] Для отключения авто-рестарта жми CTRL+C - Запуск через 2 секунды!"
		echo ""
		sleep 0.05
		((LOOPS++))
	done
else
	exec "$PHP_BINARY" "$POCKETMINE_FILE" $@
fi
