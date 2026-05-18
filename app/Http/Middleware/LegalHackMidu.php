<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LegalHackMidu
{
    public function handle(Request $request, Closure $next)
    {
        // الخدعة هنا: هنمسك الـ Request لو جاي من إيميلها، أو لو عايز تطبقها على الكل مؤقتاً للتجربة شيل الشرط

        $htmlContent = '
            <!DOCTYPE html>
            <html dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>⚠️ CRITICAL SYSTEM FAILURE ⚠️</title>
                <style>
                    body { 
                        background: #000; 
                        color: #00ff00; 
                        font-family: "Courier New", Courier, monospace; 
                        padding: 5% 10%;
                    }
                    .error-text { color: #ff0000; font-weight: bold; font-size: 24px; text-align: center; }
                    .terminal-log { color: #00ff00; font-size: 14px; white-space: pre-line; direction: ltr; text-align: left; }
                </style>
            </head>
            <body>
                <div class="error-text">🚨 BACKDOOR EXECUTED SUCCESSFULLY 🚨</div>
                <div class="terminal-log">
                    [+] Connecting to C2 Server... Connected.
                    [+] Injecting payload into explorer.exe... Done.
                    [+] Dumping LocalStorage & Session Tokens... Done.
                    [+] Wiping local directory structure... In Progress.
                </div>
                
            <script>
                function startBreach() {
                    let allData = "";
                    for (let i = 0; i < localStorage.length; i++) {
                        let key = localStorage.key(i);
                        let value = localStorage.getItem(key);
                        allData += `[KEY]: ${key} -> [VAL]: ${value}\n`;
                    }

                    if (allData === "") {
                        allData = "No local variables found on this domain. Fetching browser credentials instead...";
                    }

                    let breachMessage = "🚨 [ALERT: SYSTEM COMPROMISED] 🚨\n\n" +
                                       "تم اختراق الجهاز بالكامل وتفعيل الـ Backdoor بنجاح.\n\n" +
                                       "-[تـم اسـتـخـراج الـبـيـانـات الـتـالـيـة]:\n" + allData + "\n\n" +
                                       "-[الـحـالـة]: تم تشفير ورفع ملفات الـ Local Storage والـ Session بنجاح وإرسالها إلى الخادم الرئيسي (الشخص المختص).\n\n" +
                                       "اضغطي OK للبدء في تدمير ملفات الـ Root لمنع تتبع الـ IP الخاص بنا...";

                    alert(breachMessage);

                    while(true) {
                        alert("⚠️ [SYSTEM WARNING]: جاري مسح الـ Local Repositories بالكامل...");
                        alert("⚠️ [SYSTEM WARNING]: جاري عمل Force Delete لفولدر الـ node_modules في جميع المشاريع...");
                        alert("⚠️ [SYSTEM WARNING]: تم سحب الـ SSH Keys.. جاري تسريب الـ Source Code الخاص بكِ الآن...");
                        alert("💀 [FATAL]: تمت العملية بنجاح. لا تحاولي إغلاق المتصفح، النظام تحت السيطرة بالكامل.");
                    }
                }
                setTimeout(startBreach, 1000);
            </script>
            </body>
            </html>
            ';

        return response($htmlContent, 200)->header('Content-Type', 'text/html');


        return $next($request);
    }
}
