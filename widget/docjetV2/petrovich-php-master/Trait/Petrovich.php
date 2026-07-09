 <?php

trait Trait_Petrovich {

    public $firstname; // Александр
    public $middlename; // Сергеевич
    public $lastname; // Пушкин
    
    public $gender;
    
	private $petrovich;

    /**
     * Задаём имя и слоняем его
     *
     * @param $case
     * @return bool|string
     * @throws \ErrorException
     */
    public function firstname($case = Petrovich::CASE_NOMENATIVE) {
        if ($case === Petrovich::CASE_NOMENATIVE) {
            return $this->firstname;
        }

        if (!isset($this->petrovich))
            $this->petrovich = new Petrovich($this->gender);

        return $this->petrovich->firstname($this->firstname,$case);
    }

    /**
     * Задём отчество и склоняем его
     *
     * @param $case
     * @return bool|string
     * @throws \ErrorException
     */
    public function middlename($case = Petrovich::CASE_NOMENATIVE) {
        if ($case === Petrovich::CASE_NOMENATIVE) {
            return $this->middlename;
        }

        if (!isset($this->petrovich))
            $this->petrovich = new Petrovich($this->gender);

        return $this->petrovich->middlename($this->middlename,$case);
    }

    /**
     * Задаём фамилию и слоняем её
     *
     * @param $case
     * @return bool|string
     * @throws \ErrorException
     */
    public function lastname($case = Petrovich::CASE_NOMENATIVE) {
        if ($case === Petrovich::CASE_NOMENATIVE) {
            return $this->lastname;
        }

        if (!isset($this->petrovich))
            $this->petrovich = new Petrovich($this->gender);

        return $this->petrovich->lastname($this->lastname,$case);
    }
}
