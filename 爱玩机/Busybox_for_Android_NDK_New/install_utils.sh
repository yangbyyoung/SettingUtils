pkgName="$1"
userId="$2"
if [ -z "$pkgName" ]; then
  pkgName="com.byyoung.setting"
fi
if [ -z "$userId" ]; then
  filesDir=/data/data/${pkgName}/files
else
  filesDir=/data/user/$userId/${pkgName}/files
fi
installDir=$filesDir/busybox
installCacheDir=$installDir/busybox/cache

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
  busyBoxFile=${installCacheDir}/busybox/busybox-arm64
  adbFile=${installCacheDir}/adb/adb-arm
  fastBootFile=${installCacheDir}/fastboot/fastboot-arm

elif [[ "$ARCH" == arm ]]; then
  busyBoxFile=${installCacheDir}/busybox/busybox-arm
  adbFile=${installCacheDir}/adb/adb-arm
  fastBootFile=${installCacheDir}/fastboot/fastboot-arm

elif [[ "$ARCH" == x86_64 ]]; then
  busyBoxFile=${installCacheDir}/busybox/busybox-arm
  adbFile=${installCacheDir}/adb/adb-x86
  fastBootFile=${installCacheDir}/fastboot/fastboot-x86

elif [[ "$ARCH" == x86 ]]; then
  busyBoxFile=${installCacheDir}/busybox/busybox-x86
  adbFile=${installCacheDir}/adb/adb-x86
  fastBootFile=${installCacheDir}/fastboot/fastboot-x86

else
  busyBoxFile=${installCacheDir}/busybox/busybox-arm64
  adbFile=${installCacheDir}/adb/adb-arm
  fastBootFile=${installCacheDir}/fastboot/fastboot-arm
  echo "Unknown!"
fi

chmod -R 0777 "${busyBoxFile}"
chmod -R 0777 "${adbFile}"
chmod -R 0777 "${fastBootFile}"

cp -p -r "${adbFile}" "${installDir}"/adb
cp -p -r "${fastBootFile}" "${installDir}"/fastboot
cp -p -r "${installCacheDir}"/others/* "${installDir}"

if [ -f "$busyBoxFile" ]; then
  chmod -R 0777 "$busyBoxFile"
  cp -p -r "$busyBoxFile" "${installDir}"/busybox
  cd "${installDir}" || exit 127
  for applet in $($busyBoxFile --list); do
    case "$applet" in
    "sh" | "busybox" | "svc" | "date")
      echo 'Skip' >/dev/null
      ;;
    *)
      $busyBoxFile ln -sf busybox "$applet"
      ;;
    esac
  done
  echo '' >busybox_installed

else
  echo "获取BusyBox路径异常" 1>&2
  exit 127

fi

chmod -R 0777 "${filesDir}"/*
chmod -R 0777 "${installDir}"/*
rm -rf "${installCacheDir}"
