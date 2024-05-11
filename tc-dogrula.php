<?php

function karakterDuzelt($yazi){
    $ara=array("ç","i","ı","ğ","ö","ş","ü");
    $degistir=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
    $yazi=str_replace($ara,$degistir,$yazi);
    $yazi=strtoupper($yazi);
    return $yazi;
}

$tcKimlikNo = isset($_COOKIE['tcKimlikNo']) ? $_COOKIE['tcKimlikNo'] : '';
$ad = isset($_COOKIE['ad']) ? $_COOKIE['ad'] : '';
$soyad = isset($_COOKIE['soyad']) ? $_COOKIE['soyad'] : '';
$dogumYili = isset($_COOKIE['dogumYili']) ? $_COOKIE['dogumYili'] : '';

// Eğer kullanıcı giriş yapmışsa ve adı doluysa, hoş geldin mesajını göster
if (!empty($ad)) {
    echo "<span>Hoş geldin $ad</span><br>";
} else {
    // Kullanıcı giriş yapmamışsa veya adı yoksa, formu göster
    if (isset($_POST['IsimDogrula'])) {
        $tcKimlikNo = $_POST['TC'];
        $ad = karakterDuzelt(trim($_POST["AD"]));
        $soyad = karakterDuzelt(trim($_POST['SOYAD']));
        $dogumYili = $_POST['DOGUMYILI'];

        if (preg_match("/^[0-9]{11}$/", $tcKimlikNo) && preg_match("/^[a-zA-ZğüşıöçĞÜŞİÖÇ]+$/", $ad) && preg_match("/^[a-zA-ZğüşıöçĞÜŞİÖÇ]+$/", $soyad) && preg_match("/^[0-9]{4}$/", $dogumYili)) {
            try {
                $veriler = array(
                    'TCKimlikNo' => $tcKimlikNo,
                    'Ad' => $ad,
                    'Soyad' => $soyad,
                    'DogumYili' => $dogumYili
                );
                $baglan = new SoapClient("https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL");
                $sonuc = $baglan->TCKimlikNoDogrula($veriler);

                if ($sonuc->TCKimlikNoDogrulaResult) {
                    setcookie("ad", $ad, time() + (86400 * 30), "/"); 
                    setcookie("soyad", $soyad, time() + (86400 * 30), "/"); 
                    setcookie("tcKimlikNo", $tcKimlikNo, time() + (86400 * 30), "/");
                    setcookie("dogumYili", $dogumYili, time() + (86400 * 30), "/");
                    echo "<span>Hoş geldin $ad</span><br>";
                } else {
                    echo "Girmiş olduğunuz kimlik bilgileri yanlıştır.";
                }

            } catch (\Exception $e) {
                echo "İstek sırasında bir hata oluştu.";
            }
        } else {
            echo "Girmiş olduğunuz bilgiler geçerli değil.";
        }
    } else {
        // Formu göster
        ?>
        <!DOCTYPE html>
        <html lang="tr" dir="ltr">
        <head>
            <meta charset="utf-8">
            <title>TC Kimlik Bilgileri Doğrulama</title>
        </head>
        <body>
            <form action="" method="POST">
                <input type="text" required="" name="TC" maxlength="11" placeholder="TC Kimlik Numaranız"><br>
                <input type="text" required="" name="AD" placeholder="Adınız"><br>
                <input type="text" required="" name="SOYAD" placeholder="Soyadınız"><br>
                <input type="text" required="" name="DOGUMYILI" maxlength="4" placeholder="Doğum Yılınız"><br>
                <button type="submit" name="IsimDogrula">GİRİŞ YAP</button>
            </form>
            <hr>
        </body>
        </html>
        <?php
    }
}

?>
