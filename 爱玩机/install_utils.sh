pkgName="$1"
userId="$2"
if [ -z "$pkgName" ]; then
  pkgName="com.byyoung.setting"
fi

if [ -z "$userId" ]; then
  filesPath=/data/data/${pkgName}/files
  box_path=$filesPath/busybox
  install_path=$filesPath/busybox
  cache_path=$filesPath/busybox/cache
  donatePic=${cache_path}/doantPic
  pictures=${cache_path}/Pictures

else
  filesPath=/data/user/$userId/${pkgName}/files
  box_path=$filesPath/busybox
  install_path=$filesPath/busybox
  cache_path=$filesPath/busybox/cache
  donatePic=${cache_path}/doantPic
  pictures=${cache_path}/Pictures

fi

abi=$(getprop ro.product.cpu.abi)

case $abi in
arm64*) ARCH=arm64 ;;
arm*) ARCH=arm ;;
x86_64*) ARCH=x86_64 ;;
x86*) ARCH=x86 ;;
mips64*) ARCH=mips64 ;;
mips*) ARCH=mips ;;
*) ARCH=arm64 ;;
esac

if [[ "$ARCH" == arm64 ]]; then
  busyboxpath=${cache_path}/busybox/busybox-arm64
  adbpath=${cache_path}/adb/adb-arm
  fastbootpath=${cache_path}/fastboot/fastboot-arm

elif [[ "$ARCH" == arm ]]; then
  busyboxpath=${cache_path}/busybox/busybox-arm
  adbpath=${cache_path}/adb/adb-arm
  fastbootpath=${cache_path}/fastboot/fastboot-arm

elif [[ "$ARCH" == x86_64 ]]; then
  busyboxpath=${cache_path}/busybox/busybox-arm
  adbpath=${cache_path}/adb/adb-x86
  fastbootpath=${cache_path}/fastboot/fastboot-x86

elif [[ "$ARCH" == x86 ]]; then
  busyboxpath=${cache_path}/busybox/busybox-x86
  adbpath=${cache_path}/adb/adb-x86
  fastbootpath=${cache_path}/fastboot/fastboot-x86

elif [[ "$ARCH" == mips64 ]]; then
  busyboxpath=${cache_path}/busybox/busybox-mips
  adbpath=${cache_path}/adb/adb-arm
  fastbootpath=${cache_path}/fastboot/fastboot-arm

elif [[ "$ARCH" == mips ]]; then
  busyboxpath=${cache_path}/busybox-mips64
  adbpath=${cache_path}/adb/adb-arm
  fastbootpath=${cache_path}/fastboot/fastboot-arm

else
  busyboxpath=${cache_path}/busybox/busybox-arm64
  adbpath=${cache_path}/adb/adb-arm
  fastbootpath=${cache_path}/fastboot/fastboot-arm
  echo "Unknown!"

fi

chmod -R 0777 "${busyboxpath}"
chmod -R 0777 "${adbpath}"
chmod -R 0777 "${fastbootpath}"

cp -p -r "${donatePic}" "$filesPath"
cp -p -r "${pictures}" "$filesPath"
cp -p -r "${adbpath}" "${box_path}"/adb
cp -p -r "${fastbootpath}" "${box_path}"/fastboot
cp -p -r "${cache_path}"/others/* "${box_path}"

function busybox_install() {
  systemBinPath=/system/bin
  busyboxPath="${install_path}"/busybox
  chmod 0777 "${busyboxPath}"
  ${busyboxPath} --install -s ${install_path}
  for file in $(ls ${systemBinPath}); do
    if [ "$file" != "unzip" ] && [ "$file" != "busybox" ] && [ "$file" != "tar" ]; then
      [ ! -L ${systemBinPath}/$file ] && rm -rf $install_path/$file 2>/dev/null
    fi

  done
}
if [ ! "$install_path" == "" ]; then
  mkdir -p "${install_path}"
  cp -p -r "${busyboxpath}" "${install_path}"/busybox
  cd "$install_path" || exit 127
  if [[ -f busybox ]]; then
    busybox_install
  fi
else
  echo "获取BusyBox路径异常" 1>&2
  exit 127

fi

chmod -R 0777 ${box_path}/*
chmod -R 0777 ${filesPath}/*

rm -rf "${cache_path}"


