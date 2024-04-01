<?php


namespace BookneticApp\Backend\Appointments\Helpers;


class RangeService
{
    public static function zoom($input, $outputSize) {
        $input = empty($input) ? [false] : $input;
        $ratio = $outputSize / count($input);
        $output = array();

        for ($i = 0; $i < $outputSize; $i++) {
            $value = false;

            $from = $i / $ratio;
            $inc = max(1, 1.0 / $ratio);

            for ($j = floor($from); $j < floor($from + $inc); $j++) {
                $value = $value || $input[$j];
            }

            $output[] = $value ? 1 : 0;
        }

        return $output;
    }

    public static function orArr($a, $b)
    {
        $result = [];
        for ($i = 0; $i < min(count($a), count($b)); $i++)
        {
            $result[] = ($a[$i] || $b[$i]) ? 1 : 0;
        }
        return $result;
    }

    public static function merge_ranges($ranges)
    {
        usort($ranges, function ($a, $b) {
            $d0 = $a[0] - $b[0];
            if ($d0) return $d0;
            return $a[1] - $b[1];
        });

        $result = [];

        foreach ($ranges as $range)
        {
            if (!empty($result))
            {
                $result_last_index = count($result) - 1;
                $result_last = $result[$result_last_index];

                if ($range[0] >= $result_last[0] && $range[0] <= $result_last[1])
                {
                    $result[$result_last_index][1] = max($range[1], $result_last[1]);
                    continue;
                }
            }

            $result[] = $range;
        }

        return $result;
    }

    public static function diff_ranges($a, $b)
    {
        foreach ($b as $b_item)
        {
            $result = [];
            foreach ($a as $a_item)
            {
                $d = self::diff_range($a_item, $b_item);
                $result = array_merge($result, $d);
            }
            $a = $result;
        }

        return $a;
    }

    public static function diff_range($a, $b)
    {
        if ($b[1] < $a[0] || $b[0] > $a[1])
        {
            return [ $a ];
        }
        if ($a[0] >= $b[0] && $a[1] <= $b[1])
        {
            return [];
        }
        else if ($b[0] > $a[0] && $b[1] < $a[1])
        {
            return [ [$a[0], $b[0]], [$b[1], $a[1]] ];
        }
        else if ($a[0] >= $b[0])
        {
            return [ [$b[1], $a[1]] ];
        }
        else if ($a[1] > $b[0])
        {
            return [ [$a[0], $b[0]] ];
        }

        return [ $a ];
    }

    public static function shorten_ranges_end($a, $duration)
    {
        for ($i = 0; $i < count($a); $i++)
        {
            $a[$i][1] = $a[$i][1] - $duration;
        }

        $result = [];

        foreach ($a as $item) {
            if ($item[1] >= $item[0])
                $result[] = $item;
        }

        return $result;
    }

    public static function point_exists_in_ranges($ranges, $point)
    {
        foreach ($ranges as $range)
        {
            if ($point >= $range[0] && $point <= $range[1])
                return true;
        }

        return false;
    }

    public static function range_overlap_with_ranges($ranges, $range)
    {
        foreach ($ranges as $i)
        {
            if (self::range_overlap_with_range($i, $range))
                return $i;
        }

        return false;
    }

    public static function range_overlap_with_range($a, $b)
    {
        return !($b[1] <= $a[0] || $b[0] >= $a[1]);
    }

    public static function ranges_contains_range($ranges, $range)
    {
        foreach ($ranges as $r)
        {
            if ($range[0] >= $r[0] && $range[1] <= $r[1])
                return true;
        }

        return false;
    }

}




















