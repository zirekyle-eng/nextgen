<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Mail\StudentApprovalNotification;
use App\Models\Student;
use App\User;
use App\Models\MyClass;
use App\Models\Section;

$student = new Student(['id'=>1,'first_name'=>'Ali','last_name'=>'Khan','full_name'=>'Ali Khan']);
$parentUser = new User(['email'=>'parent@example.com']);
$studentUser = new User(['email'=>'student@example.com']);
$myClass = new MyClass(['id'=>1,'name'=>'A Level']);
$section = new Section(['id'=>1,'name'=>'Section A']);

$mail = new StudentApprovalNotification($student, $parentUser, $studentUser, $myClass, $section);

$refRender = new ReflectionMethod($mail, 'renderPdf');
$refRender->setAccessible(true);

$dataRef = new ReflectionMethod($mail, 'acceptancePdfData');
$dataRef->setAccessible(true);
$data = $dataRef->invoke($mail);

try {
    $out = $refRender->invoke($mail, 'pdfs.student-acceptance-letter', $data);
    if ($out) {
        file_put_contents('rendered_direct.pdf', $out);
        echo "renderPdf: OK, wrote rendered_direct.pdf (".strlen($out)." bytes)\n";
    } else {
        echo "renderPdf returned null\n";
    }
} catch (Throwable $e) {
    echo "renderPdf threw: " . $e->getMessage() . "\n";
}

$refStyled = new ReflectionMethod($mail, 'buildStyledPdf');
$refStyled->setAccessible(true);
try {
    $styled = $refStyled->invoke($mail, 'Letter of Acceptance', $data['academyName'], $mail->acceptanceFallbackLines($data), []);
    if ($styled) {
        file_put_contents('styled_direct.pdf', $styled);
        echo "buildStyledPdf: OK, wrote styled_direct.pdf (".strlen($styled)." bytes)\n";
    } else {
        echo "buildStyledPdf returned null\n";
    }
} catch (Throwable $e) {
    echo "buildStyledPdf threw: " . $e->getMessage() . "\n";
}
