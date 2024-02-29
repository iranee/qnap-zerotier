#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="zerotier"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
APACHE_ROOT=/share/`/sbin/getcfg SHARE_DEF defWeb -d Qweb -f /etc/config/def_share.info`

if [ `/sbin/getcfg "QWEB" "Enable" -d 1` = 0 ]; then
	echo "Web服务器尚未启用，请前往[控制台]→[应用程序]→[Web服务器]开启"
	/sbin/log_tool -t1 -uSystem -p127.0.0.1 -mlocalhost -a "[ZertTior] Web服务尚未启用，请前往[控制台]→[应用程序]→[Web服务器]开启，并重启 [ZertTior]。"
fi

case "$1" in
  start)
    modprobe tun
    ENABLED=$(/sbin/getcfg $QPKG_NAME Enable -u -d FALSE -f $CONF)
    if [ "$ENABLED" != "TRUE" ]; then
        echo "$QPKG_NAME is disabled."
        exit 1
    fi
	/bin/ln -sf $QPKG_ROOT/ /var/lib/zerotier-one
	/bin/ln -sf $QPKG_ROOT/zerotier-one /usr/bin/zerotier-cli
	/bin/ln -sf $QPKG_ROOT/zerotier-one /usr/sbin/zerotier-cli
    /bin/ln -sf $QPKG_ROOT/web $APACHE_ROOT/zerotier
	/bin/chmod -Rf 777 $QPKG_ROOT/*

	$QPKG_ROOT/zerotier-one $QPKG_ROOT -d &
	$QPKG_ROOT/zerotierconfig >&1 & disown
    ;;
	
	stop)
  	killall -9 zerotierconfig
  	killall -9 zerotier-one
	rm -f $APACHE_ROOT/zerotier
	rm -f /var/lib/zerotier-one
	rm -f /usr/sbin/zerotier-cli
	rm -f /usr/bin/zerotier-cli
	;;

  restart)
    $0 stop
    $0 start
    ;;

  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
esac

exit 0
