<?php
namespace History\Services;

use Illuminate\Support\Arr;

class IdentityExtractor
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var array
     */
    protected $emails = [];

    /**
     * @var array
     */
    protected $names = [];

    /**
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
        $emails = str_replace('(original)', '', $emails);

        $names = $this->extractEmails($emails);
        $this->extractNames($names);

        // Cleanup dead results
        $this->emails = array_values(array_filter($this->emails));
        $this->names  = array_values(array_filter($this->names));

        // Combine informations
        $identities = [];
        $count      = count($this->emails) ?: count($this->names);
        for ($i = 0; $i <= $count; ++$i) {
            $identities[] = array_filter([
                'email'     => Arr::get($this->emails, $i),
                'full_name' => Arr::get($this->names, $i),
            ]);
        }

        return array_filter($identities);
    }

    /**
     * @param string $emails
     *
     * @return string
     */
    protected function extractEmails($emails)
    {
        // Try to split off emails
        $names  = $emails;
        $emails = preg_split('/[\s,]+/', $emails);
        foreach ($emails as $key => $email) {

            // Check if email is valid, if not
            // throw it away
            $email = trim($email);
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
            $names = str_replace($email, '', $names);

            $this->emails[$key] = $email;
        }

        return $names;
    }

    /**
     * @param string $names
     */
    protected function extractNames($names)
    {
        $names = preg_split('/(,|  |\n)/', $names);
        $names = array_filter($names);
        foreach ($names as $key => $name) {
            $name = trim($name);

            // Special case for that one guy who
            // put his whole resume as name
            if (strpos($name, 'Watson Research') || strlen($name) <= 3) {
                continue;
            }

            $this->names[$key] = $name;
        }
    }
}
