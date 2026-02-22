<?php

namespace App\Traits;

trait OTPTrait
{
    /**
     * Generate a random numeric OTP.
     *
     * @param int $length
     * @return string
     */
    public function generateOtp(int $length = 6): string
    {
        // Ensure OTP length is at least 4 digits
        if ($length < 4) {
            $length = 4;
        }

        // Generate random numeric OTP
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;

        return (string) random_int($min, $max);
    }
}
