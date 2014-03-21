<?php

namespace devilcius\Cuaderno19Bundle\Lib;

class Cuaderno19
{

    private $presenter = array(
        "nif" => "",
        "suffix" => "",
        "name" => "",
        "bank" => "",
        "branch" => "",
        "total" => 0,
        "ordNumber" => 0,
        "recNumber" => 0,
        "regNumber" => 0
    );
    private $applicants = array();
    private $individual = array();
    private $procedure;

    /**
     * 
     * @param type $nif presenter nif
     * @param type $suffix presenter suffix
     * @param type $name presenter name
     * @param type $bank presenter bank
     * @param type $branch presenter bank branch
     * @return int presentor id (only one presentor by remittance)
     */
    public function configurePresentator($nif, $suffix, $name, $bank, $branch, $procedure)
    {
        $this->presenter["nif"] = strtoupper($nif);
        $this->presenter["suffix"] = strtoupper($suffix);
        $this->presenter["name"] = strtoupper($name);
        $this->presenter["bank"] = strtoupper($bank);
        $this->presenter["branch"] = strtoupper($branch);
        $this->presenter["total"] = 0;
        $this->presenter["ordNumber"] = 0;
        $this->presenter["recNumber"] = 0;
        $this->presenter["regNumber"] = 2;
        $this->procedure = $procedure;

        return 0;
    }

    /**
     * 
     * @param type $nif appliccant nif
     * @param type $suffix applicant suffix
     * @param type $name applicant name
     * @param type $bank applicant bank
     * @param type $branch applicant bank branch
     * @param type $cd applicant account control digit
     * @param type $account applicant account number (CCC)
     * @return int applicant generated id
     */
    public function addApplicant($nif, $suffix, $name, $bank, $branch, $cd, $account)
    {
        $this->applicants[] = array(
            "nif" => strtoupper($nif),
            "suffix" => strtoupper($suffix),
            "name" => strtoupper($name),
            "bank" => strtoupper($bank),
            "branch" => strtoupper($branch),
            "cd" => strtoupper($cd),
            "account" => strtoupper($account),
            "total" => 0,
            "recNumber" => 0,
            "regNumber" => 2
        );

        return count($this->applicants) - 1;
    }

    /**
     * 
     * @param int $applicantId
     * @param string $reference debtor reference
     * @param type $name debtor name
     * @param type $bank debtor bank
     * @param type $branch debtor bank branch
     * @param type $cd debtor control digit
     * @param type $account debtor account
     * @param type $amount invoice amount
     * @param type $concept invoice concept
     * @return int receipt ID
     */
    public function addReceipt($applicantId, $reference, $name, $bank, $branch, $cd, $account, $amount, $concept)
    {
        $this->individual[$applicantId][] = array(
            "ref" => strtoupper($reference),
            "name" => strtoupper($name),
            "bank" => strtoupper($bank),
            "branch" => strtoupper($branch),
            "cd" => strtoupper($cd),
            "account" => strtoupper($account),
            "amount" => $amount,
            "concept" => strtoupper($concept)
        );

        return count($this->individual) - 1;
    }

    /**
     * 
     * @return string (cuaderno 19)
     */
    public function generateRemittance()
    {
        $remittance = "";

        $remittance.= $this->generatePresenter($this->presenter["nif"], $this->presenter["suffix"], $this->presenter["name"], $this->presenter["bank"], $this->presenter["branch"], date("dmy"));

        foreach ($this->applicants as $k => $v) {
            $remittance.= $this->generateApplicant($v["nif"], $v["suffix"], $v["name"], $v["bank"], $v["branch"], $v["cd"], $v["account"], date("dmy"), date("dmy"));
            foreach ($this->individual[$k] as $r) {
                $remittance.= $this->generateIndividual($v["nif"], $v["suffix"], $r["ref"], $r["name"], $r["bank"], $r["branch"], $r["cd"], $r["account"], $this->convertAmount($r["amount"]), $r["concept"]);

                $this->applicants[$k]["recNumber"] ++;
                $this->presenter["recNumber"] ++;
                $this->applicants[$k]["regNumber"] ++;
                $this->presenter["regNumber"] ++;
                $this->applicants[$k]["total"]+=number_format($r["amount"], 2, '.', '');
                $this->presenter["total"]+=number_format($r["amount"], 2, '.', '');
            }
            $remittance.= $this->generateTotalOrdenante($v["nif"], $v["suffix"], $this->convertAmount($this->applicants[$k]["total"]), $this->applicants[$k]["recNumber"], $this->applicants[$k]["regNumber"]);
            $this->presenter["ordNumber"] ++;
        }
        $this->presenter["regNumber"]+=$this->presenter["ordNumber"] * 2;

        $remittance.= $this->generateGeneralTotal($this->presenter["nif"], $this->presenter["suffix"], $this->convertAmount($this->presenter["total"]), $this->presenter["ordNumber"], $this->presenter["recNumber"], $this->presenter["regNumber"]);

        return $remittance;
    }

    private function generateGeneralTotal($nif, $sufijo, $importe, $nOrd, $nRec, $nReg)
    {
        $texto = "5980";
        $texto.= $this->fillLeft($nif, " ", 9);
        $texto.= $this->fillLeft($sufijo, 0, 3);
        $texto.= $this->fillLeft("", " ", 12);
        $texto.= $this->fillLeft("", " ", 40);
        $texto.= $this->fillLeft($nOrd, 0, 4);
        $texto.= $this->fillLeft("", " ", 16);
        $texto.= $this->fillLeft($importe, 0, 10);
        $texto.= $this->fillLeft("", " ", 6);
        $texto.= $this->fillLeft($nRec, 0, 10);
        $texto.= $this->fillLeft($nReg, 0, 10);
        $texto.= $this->fillLeft("", " ", 20);
        $texto.= $this->fillLeft("", " ", 18);
        $texto.= "\r\n";

        return $texto;
    }

    private function generateTotalOrdenante($nif, $sufijo, $importe, $nRec, $nReg)
    {
        $texto = "5880";
        $texto.= $this->fillLeft($nif, " ", 9);
        $texto.= $this->fillLeft($sufijo, 0, 3);
        $texto.= $this->fillLeft("", " ", 12);
        $texto.= $this->fillLeft("", " ", 40);
        $texto.= $this->fillLeft("", " ", 20);
        $texto.= $this->fillLeft($importe, 0, 10);
        $texto.= $this->fillLeft("", " ", 6);
        $texto.= $this->fillLeft($nRec, 0, 10);
        $texto.= $this->fillLeft($nReg, 0, 10);
        $texto.= $this->fillLeft("", " ", 20);
        $texto.= $this->fillLeft("", " ", 18);
        $texto.= "\r\n";

        return $texto;
    }

    private function generateIndividual($nif, $sufijo, $ref, $nombre, $entidad, $oficina, $dc, $cuenta, $importe, $concepto)
    {
        $texto = "5680";
        $texto.= $this->fillLeft($nif, " ", 9);
        $texto.= $this->fillLeft($sufijo, 0, 3);
        $texto.= $this->fillLeft($ref, 0, 12);
        $texto.= $this->fillRight($nombre, " ", 40);
        $texto.= $this->fillLeft($entidad, " ", 4);
        $texto.= $this->fillLeft($oficina, " ", 4);
        $texto.= $this->fillLeft($dc, 0, 2);
        $texto.= $this->fillLeft($cuenta, 0, 10);
        $texto.= $this->fillLeft($importe, 0, 10);
        $texto.= $this->fillLeft("", " ", 16);
        $texto.= $this->fillRight($concepto, " ", 40);
        $texto.= $this->fillLeft("", " ", 8);
        $texto.= "\r\n";

        return $texto;
    }

    private function generatePresenter($nif, $sufijo, $nombre, $entidad, $oficina, $fecha)
    {
        $texto = "5180";
        $texto.= $this->fillLeft($nif, " ", 9);
        $texto.= $this->fillLeft($sufijo, 0, 3);
        $texto.= $this->fillLeft($fecha, " ", 6);
        $texto.= $this->fillLeft("", " ", 6);
        $texto.= $this->fillRight($nombre, " ", 40);
        $texto.= $this->fillLeft("", " ", 20);
        $texto.= $this->fillLeft($entidad, " ", 4);
        $texto.= $this->fillLeft($oficina, " ", 4);
        $texto.= $this->fillLeft("", " ", 12);
        $texto.= $this->fillLeft("", " ", 40);
        $texto.= $this->fillLeft("", " ", 14);
        $texto.= "\r\n";

        return $texto;
    }

    private function generateApplicant($nif, $sufijo, $nombre, $entidad, $oficina, $dc, $cuenta, $fecha, $fechaCargo)
    {
        $texto = "5380";
        $texto.= $this->fillLeft($nif, " ", 9);
        $texto.= $this->fillLeft($sufijo, 0, 3);
        $texto.= $this->fillLeft($fecha, " ", 6);
        $texto.= $this->fillLeft($fechaCargo, " ", 6);
        $texto.= $this->fillRight($nombre, " ", 40);
        $texto.= $this->fillLeft($entidad, " ", 4);
        $texto.= $this->fillLeft($oficina, " ", 4);
        $texto.= $this->fillLeft($dc, 0, 2);
        $texto.= $this->fillLeft($cuenta, 0, 10);
        $texto.= $this->fillLeft("", " ", 8);
        $texto.= $this->fillLeft($this->procedure, 0, 2);
        $texto.= $this->fillLeft("", " ", 10);
        $texto.= $this->fillLeft("", " ", 40);
        $texto.= $this->fillLeft("", " ", 14);
        $texto.= "\r\n";

        return $texto;
    }

    private function convertAmount($importe)
    {
        return number_format($importe, 2, "", "");
    }

    private function fillRight($texto, $caracter, $longitud, $cortar = true)
    {
        if ($cortar) {
            $texto = substr($texto, 0, $longitud);
        }

        for ($i = strlen($texto); $i < $longitud; $i++) {
            $texto = $texto . $caracter;
        }

        return $texto;
    }

    private function fillLeft($texto, $caracter, $longitud, $cortar = true)
    {
        if ($cortar) {
            $texto = substr($texto, 0, $longitud);
        }

        for ($i = strlen($texto); $i < $longitud; $i++) {
            $texto = $caracter . $texto;
        }

        return $texto;
    }

}
