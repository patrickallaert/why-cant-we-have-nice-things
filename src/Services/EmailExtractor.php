<?php
namespace History\Services;

class EmailExtractor
{
    /**
     * @var string
     */
    protected $string;

    /**
     * EmailExtractor constructor.
     *
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @return string[]
     */
    public function extract()
    {
        // Workaround some anti-bot measures
        $emails = preg_replace('/[<>\(\)]/', '', $this->string);
        $emails = preg_replace('/([ #]at[# ])/', '@', $emails);
        $emails = str_replace(' . ', '.', $emails);

        // Try to split off emails
        $emails = preg_split('/[\s,]+/', $emails);
        foreach ($emails as $key => $email) {

            // Check if email is valid, if not
            // throw it away
            $email = trim($email);
            $email = preg_replace('/@(.+)/', '@php.net', $email);
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;

            $emails[$key] = $email;
        }

        return array_values(array_filter($emails));
    }
}
