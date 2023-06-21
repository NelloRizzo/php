<?php
namespace Drupal\form_api_example\Form;

class FiscalCodeService implements FiscalCodeServiceInterface
{
    private function getConsAndVowels(string $text)
    {
        $result = ['cons' => '', 'vow' => ''];
        for ($i = 0; $i < strlen($text); $i++) {
            $c = strtoupper($text[$i]);
            if ($c >= 'A' && $c <= 'Z')
                if ($c == 'A' || $c == 'E' || $c == 'I' || $c == 'O' || $c == 'U') {
                    $result['vow'] .= $c;
                } else
                    $result['cons'] .= $c;
        }
        return $result;
    }
    private function handleLastName(string $ln): string
    {
        $r = $this->getConsAndVowels($ln);
        return substr("{$r['cons']}{$r['vow']}XXX", 0, 3);
    }
    private function handleFirstName(string $fn): string
    {
        $r = $this->getConsAndVowels($fn);
        if (strlen($r['cons']) > 3)
            $r['cons'] = $r['cons'][0] . substr($r['cons'], 2, 2);
        return substr("{$r['cons']}{$r['vow']}XXX", 0, 3);
    }
    private function handleBirthday(\DateTimeImmutable $bd, Gender $ge): string
    {
        $m = "ABCDEHLMPRST";
        $d = (int) $bd->format('d') + ($ge == Gender::Male ? 0 : 40);
        if ($d < 10)
            $d = '0' . $d;
        return $bd->format('y') . $m[(int) $bd->format('m') - 1] . $d;
    }
    private function handleBirthCity(string $bc): string
    {
        return strtoupper($bc);
    }
    private function calcCheckCode(string $fc): string
    {
        $odds = [1, 0, 5, 7, 9, 13, 15, 17, 19, 21, 2, 4, 18, 20, 11, 3, 6, 8, 12, 14, 16, 10, 22, 25, 24, 23];
        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $c = $fc[$i];
            $depl = ($c >= '0' && $c <= '9') ? (int) $c : ord($c) - ord('A');
            $sum += $i % 2 == 0 ? $odds[$depl] : $depl;
        }
        return chr($sum % 26 + ord('A'));
    }
    public function calculateFiscalCode(PersonalData $input): string
    {
        $fc =
            $this->handleLastName($input->lastName)
            .
            $this->handleFirstName($input->firstName)
            .
            $this->handleBirthday($input->birthday, $input->gender)
            .
            $this->handleBirthCity($input->birth_city)
        ;
        $fc .= $this->calcCheckCode($fc);
        return $fc;
    }

}