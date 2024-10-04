<?php

namespace App\Support\Auth;

use Illuminate\Support\Carbon;

class IDCardSupport
{
    /**
     * 校验身份证号是否合法
     *
     * @param  string  $num  待校验的身份证号
     * @return bool
     */
    public function isValid(string $num)
    {
        // 老身份证长度15位，新身份证长度18位
        $length = strlen($num);
        if ($length == 15) { // 如果是15位身份证
            // 15位身份证没有字母
            if (! is_numeric($num)) {
                return false;
            }
            // 省市县（6位）
            $areaNum = substr($num, 0, 6);
            // 出生年月（6位）
            $dateNum = substr($num, 6, 6);
        } elseif ($length == 18) { // 如果是18位身份证

            // 基本格式校验
            if (! preg_match('/^\d{17}[0-9xX]$/', $num)) {
                return false;
            }
            // 省市县（6位）
            $areaNum = substr($num, 0, 6);
            // 出生年月日（8位）
            $dateNum = substr($num, 6, 8);
        } else { // 假身份证
            return false;
        }

        // 验证地区
        if (! $this->isAreaCodeValid($areaNum)) {
            return false;
        }

        // 验证日期
        if (! $this->isDateValid($dateNum)) {
            return false;
        }

        // 验证最后一位
        if (! $this->isVerifyCodeValid($num)) {
            return false;
        }

        return true;
    }

    /**
     *  省、直辖市校验
     *
     * @param  string  $area  省、直辖市代码
     * @return bool
     */
    public function isAreaCodeValid(string $area)
    {
        $provinceCode = substr($area, 0, 2);

        // 根据GB/T2260—999，省市代码11到65 不包含香港(81),澳门(82),台湾(83)
        if ($provinceCode >= 11 && $provinceCode <= 65) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证出生日期合法性
     *
     * @param  string  $date  日期
     * @return bool
     */
    public function isDateValid(string $date)
    {
        // 15位身份证号没有年份，这里拼上年份
        if (strlen($date) == 6) {
            $date = '19'.$date;
        }
        $year = intval(substr($date, 0, 4));
        $month = intval(substr($date, 4, 2));
        $day = intval(substr($date, 6, 2));

        // 日期基本格式校验
        if (! checkdate($month, $day, $year)) {
            return false;
        }

        // 日期格式正确，但是逻辑存在问题(如:年份大于当前年)
        $currYear = date('Y');
        if ($year > $currYear) {
            return false;
        }

        return true;
    }

    /**
     * 验证18位身份证最后一位
     *
     * @param  string  $num  待校验的身份证号
     * @return bool
     */
    public function isVerifyCodeValid(string $num)
    {
        if (strlen($num) == 18) {
            $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $tokens = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

            $checkSum = 0;
            for ($i = 0; $i < 17; $i++) {
                $checkSum += intval($num[$i]) * $factor[$i];
            }

            $mod = $checkSum % 11;
            $token = $tokens[$mod];

            $lastChar = strtoupper($num[17]);

            if ($lastChar != $token) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取性别，true 为男，false 为女
     *
     * @param  string  $num  待校验的身份证号
     */
    public function getSex(string $num): bool
    {
        if ($num[16] % 2 == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  根据身份证号码获取生日
     *  author:xiaochuan
     *
     * @param  string  $idCard  身份证号码
     * @return $birthday
     */
    public function getBirthday(string $idCard): Carbon
    {
        $bir = substr($idCard, 6, 8);
        $year = substr($bir, 0, 4);
        $month = substr($bir, 4, 2);
        $day = substr($bir, 6, 2);

        return Carbon::create($year, $month, $day);
    }

    public function getAge(string $idcard): int
    {
        // 获得出生年月日的时间戳
        $date = strtotime(substr($idcard, 6, 8));
        // 获得今日的时间戳
        $today = strtotime('today');
        // 得到两个日期相差的大体年数
        $diff = floor(($today - $date) / 86400 / 365);
        // strtotime 加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($idcard, 6, 8).' +'.$diff.'years') > $today ? ($diff + 1) : $diff;

        return $age;
    }
}
