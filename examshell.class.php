<?php

class Examshell
{
    private $userInput, $score, $startTime, $currentTime, $timeLimit, $pointsPerExercise;
    private $currentExercise = 0;
    private $exercises, $help = [];
    private $on = true;

    function __construct()
    {
        $this->parse_examshell_json();
        $this->display_welcome_message();
        // echo "pp exercises = " . $this->pointsPerExercise . PHP_EOL;
        $this->userInput = fgets(STDIN);
        if ($this->userInput == "\n") {
            $this->startTime = time();
            $this->start_exercise();
        }
        // if ($this->userInput == "time\n") {
        //     $this->currentTime = time();
        //     echo strftime("%H:%M:%S", 14400 - ($this->currentTime - $this->startTime));
        // }
    }

    private function start_exercise()
    {
        $this->create_repo();
        $this->shell_display();
    }

    private function create_repo()
    {
        $currentExerciseArray = $this->exercises[$this->currentExercise];
        $currentExerciseName = $currentExerciseArray["name"];
        $currentExerciseInstructions = $currentExerciseArray["instructions"];
        $currentExerciseShellInstructions = $currentExerciseArray["shellInstructions"];

        if (!is_dir("./" . $currentExerciseArray["name"])) {
            @system("mkdir " . $currentExerciseArray["name"]);
            @system("touch " . $currentExerciseName . "/" . $currentExerciseName . ".txt");
            file_put_contents("./" . $currentExerciseName . "/" . $currentExerciseName . ".txt", $currentExerciseInstructions);
        }

        echo $currentExerciseShellInstructions;
    }

    private function compile_and_check()
    {
        $currentExerciseArray = $this->exercises[$this->currentExercise];
        $currentExerciseName = $currentExerciseArray["name"];
        $currentExerciseExpectedOutput = $currentExerciseArray["expectedOutput"];
        $result = null;

        try {
            if (file_exists("./" . $currentExerciseName . "/" . $currentExerciseName . ".c")) {
                @system("gcc ./" . $currentExerciseName . "/" . $currentExerciseName . ".c");
                @system("./a.out > result.yo");
                @system("rm ./a.out");
                $result = fread(fopen("./result.yo", "r"), filesize("./result.yo"));
                @system("rm result.yo");
            } else {
                throw new Exception();
            }
        } catch (\Throwable $th) {
            echo "\033[0;31mERROR : .c File not found in directory !\033[0m" . PHP_EOL;
        }

        if ($result == $currentExerciseExpectedOutput) {
        } else {
            echo "\033[0;31mERROR : expected output not matching results !\033[0m" . PHP_EOL;
            echo "\033[0;31m>>>>> FAILURE :x Try again !\033[0m" . PHP_EOL . "Press enter when you're done :)" . PHP_EOL;
        }
    }

    private function shell_display()
    {
        while ($this->on == true) {
            $this->userInput = fgets(STDIN);
            if ($this->userInput == "\n") {
                // $success = false;
                $success = $this->compile_and_check();
                echo "success value = " .  $success . PHP_EOL;
                if ($success == true) {
                    $this->exercice++;
                    // $this->createRepo($this->listeExercices[$this->exercice]);
                    break;
                }
            }
        }
    }

    private function parse_examshell_json()
    {
        try {
            if (file_exists("./assets/exam.json")) {
                $json = fread(fopen("./assets/exam.json", "r"), filesize("./assets/exam.json"));
                $arrayFromJson = json_decode($json, true);
                $this->timeLimit = (int) $arrayFromJson["timeLimit"];
                $this->exercises = $arrayFromJson["exercises"];
                $this->help = $arrayFromJson["help"];
                $this->pointsPerExercise = 100 / count($this->exercises);
            } else {
                throw new Exception();
            }
        } catch (\Throwable $th) {
            echo "Fatal error : mandatory exam.json file missing" . PHP_EOL . "Exiting...";
            die;
        }
    }

    private function display_welcome_message()
    {
        @system("clear");
        $logo = fread(fopen("./assets/logo.asciiart", "r"), filesize("./assets/logo.asciiart"));
        echo $logo . PHP_EOL;
        echo "Welcome to PHP Examshell 00 ! Please read the README.md file before continuing" . PHP_EOL . "Press enter to begin..." . PHP_EOL;
    }
}