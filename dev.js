const { spawn } = require('child_process');
const fs = require('fs');

// ค้นหาไฟล์ php.exe อัตโนมัติ (รองรับ XAMPP บน Windows)
const phpPath = fs.existsSync('C:\\xampp\\php\\php.exe')
  ? 'C:\\xampp\\php\\php.exe'
  : 'php';

console.log('กำลังเริ่มต้น PHP Backend Server ที่ http://127.0.0.1:8000 ...');
const phpServer = spawn(phpPath, ['-S', '127.0.0.1:8000', '-t', 'backend'], {
  stdio: 'inherit'
});

phpServer.on('error', () => {
  console.error('ไม่พบโปรแกรม PHP กรุณาติดตั้ง XAMPP (ไว้ที่ C:\\xampp) หรือเพิ่ม PHP ลงใน PATH');
});

console.log('กำลังเริ่มต้น Vue Frontend Server ...');
const vueServer = spawn('npx', ['vue-cli-service', 'serve'], {
  stdio: 'inherit',
  shell: true
});

process.on('SIGINT', () => {
  phpServer.kill();
  vueServer.kill();
  process.exit();
});