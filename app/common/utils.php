<?

class Utils
{
    /**
     * If the value is null, then replace with the tpe that you wish
     * @param $value
     * @param null $replacementValue
     * @return null|string
     */
    static function getSafe($value, $replacementValue = null)
    {
        $replacementValue = isset($replacementValue) ? $replacementValue : '';

        if (!isset($value)) {
            return $replacementValue;
        } else {
            return $value;
        }
    }
}