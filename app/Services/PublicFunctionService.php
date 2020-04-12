<?php

namespace App\Services;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Intervention\Image\ImageManagerStatic as Image;

class PublicFunctionService
{
    public function __construct()
    {
    }

    public function mb_count_chars($input)
    {

        $l = mb_strlen($input, 'UTF-8');
        $str_result = [];
        for ($i = 0; $i < $l; $i++) {
            $char = mb_substr($input, $i, 1, 'UTF-8');
            array_push($str_result, $char);
        }
        return $str_result;
    }

    //ma hoa chuoi
    public function enCrypto($data, $key)
    {

        // $key_gen = substr(hash('sha256', $key, true), 0, 32);
        // $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        // return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key_gen, OPENSSL_RAW_DATA, $iv));

        $encrypted = CryptoJSAES::encrypt($data, $key);
        return $encrypted;
    }

    //part chuoi nguoc lai
    public function deCrypto($data, $key)
    {

        // $key_gen = substr(hash('sha256', $key, true), 0, 32);
        // $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        // return openssl_decrypt(base64_decode($encrypted), 'AES-256-CBC', $key_gen, OPENSSL_RAW_DATA, $iv);

        $decrypted = CryptoJSAES::decrypt($data, $key);
        return $decrypted;
    }

    public function saveImgBase64($data, $middle, $url_img, $width, $height)
    {

        if ($data) {
            $image = explode(';', $data);

            //get file name
            $img_name = explode('/',  $image[0]);
            $file_name = $middle . '_' . time() . '.' . $img_name[1];
            if (file_exists(public_path().$url_img)) {
              $path = public_path() . $url_img . $file_name;
              $img = Image::make(file_get_contents($data));
              $img->resize($width, $height);
              if ($img->save($path)) {
                  return $file_name;
              }
            }
        }
    }

    public function removeImageBase64($file_name, $url_img)
    {

        if ($file_name) {
            $path = public_path() . $url_img . $file_name;
            if (file_exists($path)) {
                if (unlink($path)) return true;
            }
        }
    }

    // diff date
    public function s_datediff($str_interval, $dt_menor, $dt_maior, $relative = false)
    {
        if (is_string($dt_menor)) $dt_menor = date_create($dt_menor);
        if (is_string($dt_maior)) $dt_maior = date_create($dt_maior);
        $diff = date_diff($dt_menor, $dt_maior, !$relative);

        $number_day_in_month = 28;
        $month = (int) date('m');
        $year = (int) date('Y');
        if (in_array($month, [1, 3, 5, 7, 8, 10, 12])) $number_day_in_month = 31;
        if (in_array($month, [4, 6, 9, 11])) $number_day_in_month = 30;
        if (in_array($month, [2])) if ((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) $number_day_in_month = 29;

        $total = 0;

        switch ($str_interval) {
            case "y":
                $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
                break;
            case "m":
                $total = $diff->y * 12 + $diff->m + $diff->d / $number_day_in_month + $diff->h / 24;
                break;
            case "d":
                $total = $diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d + $diff->h / 24 + $diff->i / 60;
                break;
            case "h":
                $total = ($diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d) * 24 + $diff->h + $diff->i / 60;
                break;
            case "i":
                $total = (($diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
                break;
            case "s":
                $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s;
                break;
        }
        if ($diff->invert) return -1 * $total;
        else return $total;
    }
}
