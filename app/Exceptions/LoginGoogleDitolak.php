<?php

namespace App\Exceptions;

use Exception;

/**
 * Akun Google tidak dapat dipakai untuk masuk (misalnya email belum terverifikasi).
 * Pesannya ditampilkan kepada pelanggan di halaman Masuk.
 */
class LoginGoogleDitolak extends Exception {}
