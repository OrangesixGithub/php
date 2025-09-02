<?php

namespace Orangesix\Utils\Date;

class Time
{
    /** @var string */
    private string $exp_inicio;

    /** @var string */
    private string $exp_fim;

    /** @var array */
    private array $facultativo;

    /**
     * @param string $exp_inicio
     * @param string $exp_fim
     * @param array $facultativo
     */
    public function __construct(string $exp_inicio = '08:00', string $exp_fim = '18:00', array $facultativo = [])
    {
        $this->exp_inicio = $exp_inicio;
        $this->exp_fim = $exp_fim;
        $this->facultativo = $facultativo;
    }

    /**
     * Adiciona a hora uteis parametrizada a partir da data informada
     * @param string $date_start
     * @param int|string $horas
     * @return \DateTime|null
     */
    public function addHorasUteis(string $date_start, int|string $horas): ?\DateTime
    {
        try {
            $date_start = new \DateTime(strpos($date_start, ' ') ? $date_start : $date_start . ' ' . $this->exp_inicio);
            $horas = is_string($horas) ? HoursToDecimal($horas) : $horas;
            $period = HoursToDecimal($this->exp_fim) - HoursToDecimal($this->exp_inicio); //6

            while ($this->verifyFeriadoOrFds($date_start)) {
                $date_start->add(\DateInterval::createFromDateString('+1day'));
            }

            $horasAdd = fmod($horas, $period);
            $days = floor($horas / $period);
            while ($days > 0) {
                $date_start->add(\DateInterval::createFromDateString('+1day'));
                if (!$this->verifyFeriadoOrFds($date_start)) {
                    $days--;
                }
            }

            $date_fim = new \DateTime($date_start->format('Y-m-d') . ' ' . $this->exp_fim);
            $date_start->add(\DateInterval::createFromDateString("+{$horasAdd}hours"));
            $diff = $date_fim->diff($date_start);
            if ($diff->invert == 0 && $diff->h > 0) {
                $date_start->add(\DateInterval::createFromDateString('+1day'));
                $date_start = new \DateTime($date_start->format('Y-m-d') . ' ' . $this->exp_inicio);
                $date_start->add(\DateInterval::createFromDateString("+{$diff->h}hours"));
            }
            return $date_start;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     *  Calcula em horas a diferença de horas uteis de acordo com parametro.
     *  Para data inicio e fim iguais considere especificar as horas, não será considerado o expediente no resultado apenas a diferença integral.
     * @param string $date_start
     * @param string $date_fim
     * @param bool $hours_string
     * @return mixed
     */
    public function getHorasUteis(string $date_start, string $date_fim, bool $hours_string = true): mixed
    {
        try {
            $date_start = new \DateTime(strpos($date_start, ' ') ? $date_start : $date_start . ' ' . $this->exp_inicio);
            $date_fim = new \DateTime(strpos($date_fim, ' ') ? $date_fim : $date_fim . ' ' . $this->exp_fim);

            if (HoursToMinute($date_start->format('H:i')) < HoursToMinute($this->exp_inicio)) {
                $date_start = new \DateTime($date_start->format('Y-m-d') . ' ' . $this->exp_inicio);
            }
            if (HoursToMinute($date_fim->format('H:i')) > HoursToMinute($this->exp_fim)) {
                $date_fim = new \DateTime($date_fim->format('Y-m-d') . ' ' . $this->exp_fim);
            }
            $diff = $date_start->diff($date_fim);

            /**
             * Adição das horas
             * @param array $result
             * @param \DateInterval $interval
             * @return array
             */
            $addHoras = function (array $result, \DateInterval $interval): array {
                $result['dias'] += $interval->invert == 1 ? 0 : $interval->d;
                $result['horas'] += $interval->invert == 1 ? 0 : $interval->h;
                $result['minutos'] += $interval->invert == 1 ? 0 : $interval->i;
                $result['segundos'] += $interval->invert == 1 ? 0 : $interval->s;
                return $result;
            };

            $result = [
                'dias' => 0,
                'horas' => 0,
                'minutos' => 0,
                'segundos' => 0
            ];

            //Verifica se o final é maior que data inicial
            if ($date_start->format('Y-m-d') < $date_fim->format('Y-m-d')) {
                //Diferença do primeiro dia util
                $firtsDay = new \DateTime($date_start->format('Y-m-d') . $this->exp_fim);
                $firtsDayDiff = $date_start->diff($firtsDay);
                if (!$this->verifyFeriadoOrFds($firtsDay)) {
                    $result = $addHoras($result, $firtsDayDiff);
                }

                //verifica se a diferença e mais de 1 dia
                if ($diff->days > 0) {
                    for ($i = 0; $i <= ($diff->days); $i++) {
                        $date_start->add(\DateInterval::createFromDateString('+1day'));
                        $dateDay['i'] = new \DateTime($date_start->format('Y-m-d') . $this->exp_inicio);
                        $dateDay['f'] = new \DateTime($date_start->format('Y-m-d') . $this->exp_fim);

                        if ($date_fim->format('Y-m-d') < $date_start->format('Y-m-d')) {
                            continue;
                        }

                        if ($dateDay['i']->format('Y-m-d') !== $date_fim->format('Y-m-d')) {
                            if ($this->verifyFeriadoOrFds($dateDay['i'])) {
                                continue;
                            }
                            $d = $dateDay['i']->diff($dateDay['f']);
                            $result = $addHoras($result, $d);
                        } else {
                            if ($this->verifyFeriadoOrFds($date_fim)) {
                                continue;
                            }
                            $d = $dateDay['i']->diff($date_fim);
                            if ($d->invert === 0) {
                                $result = $addHoras($result, $d);
                            }
                        }
                    }
                } else {
                    //Diferença do segundo dia util
                    $date_start->add(\DateInterval::createFromDateString('+1day'));
                    if (!$this->verifyFeriadoOrFds($date_start)) {
                        $lastDay = new \DateTime($date_start->format('Y-m-d') . $this->exp_inicio);
                        $lastDayDiff = $lastDay->diff($date_fim);
                        $result = $addHoras($result, $lastDayDiff);
                    }
                }
            } elseif (!$this->verifyFeriadoOrFds($date_start)) {
                $result = $addHoras($result, $diff);
            }

            $horas = ($result['dias'] * 24) + $result['horas'] + ($result['minutos'] / 60) + (($result['segundos'] / 60) / 60);
            return $hours_string ? DecimalToHours($horas) : $horas;
        } catch (\Exception $exception) {
            return '00:00';
        }
    }

    /**
     * Realiza a validação do feriado e final de semana
     * @param \DateTime $date
     * @return bool
     */
    private function verifyFeriadoOrFds(\DateTime $date): bool
    {
        if (GetFeriado($date->format('Y-m-d'), $this->facultativo) || $date->format('w') == 0 || $date->format('w') == 6) {
            return true;
        }
        return false;
    }
}
