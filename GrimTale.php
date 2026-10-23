<?php
define("DEBUG", false); // skip dialogue

Write("WHAT IS THE NAME OF OUR SAVIOUR AND HERO?", false);
$NAME = readline();
ClearScreen();

$STORY = "Once upon a time, there was a continent known as Grasstonia.
Long ago the land was known for its beautiful nature,
but one day an evil and grim warlord came to visit known by the name “Lord Chain”,
who plunged the continent into darkness, he resides far and deep inside the continent in his dark castle,
waiting to take more from the land for his own desires.
Thankfully, a PROPHECY exists within the land,
a PROPHECY which had predicted this tragedy.
Believed in by the inhabitants of the land, that speaks of a hero by the name ''$NAME'',
that will retrieve the land that was taken by the evil warlord, and bring peace to the land once and for
all. ";

// stats of player and enemies
$PLR = [
    "ATK" => 50,
    "HP" => 100,
    "LVL" => 0,
];

$GOBLIN = [
    "ATK" => 3,
    "HP" => 40
];

$SLIME = [
    "ATK" => 5,
    "HP" => 30
];

$SP = 0; // SP stands for save point, this is how game progress will be handled

function Write(string $STORY, bool $SKIP)
{
    if (DEBUG === true || $SKIP === true) {
        echo $STORY . PHP_EOL; // instant echo
        return;
    }

    $keys = 0;
    while (true) {
        $ch = @$STORY[$keys]; // @ to ignore string errors
        if ($ch == "") {
            echo PHP_EOL;
            break;
        }
        echo $ch;
        if ($ch == "." || $ch == ",") {
            sleep(1);
        } else {
            usleep(20000);
        }
        $keys++;
    }
}

function ClearScreen()
{
    echo "\e[H\e[J"; // this command clears the output
}

function Scenario(string $TEXT, array $OPTIONS)
{
    $CHOICES = "";

    foreach ($OPTIONS as $OPTION => $RESULT) {
        $CHOICES .= "$OPTION, ";
    }

    Write($TEXT . " : ($CHOICES)", true);

    $prompt = readline();

    foreach ($OPTIONS as $OPTION => $RESULT) {
        if ($prompt == $OPTION) {
            Write("You have chosen the option: $prompt, $RESULT", false);
            return; // found
        }
    }

    // only reached if no option matched
    ClearScreen();
    // echo "Invalid answer" . PHP_EOL;
    Scenario($TEXT, $OPTIONS); // runs the function again after invalid input
}

if (str_contains($NAME, ";") || str_contains($NAME, "/") || str_contains($NAME, "]") || str_contains($NAME, "[") || str_contains($NAME, "{") || str_contains($NAME, "}") || str_contains($NAME, "|") || is_numeric($NAME) || $NAME == "") {
    Write("THERE IS NO PLACE FOR A NAMELESS HERO HERE.", false);
    ClearScreen();
    exit;
}

Write($STORY, false);

ClearScreen();

Scenario("You find yourself in an open-area, there are 2 paths, which path will you take?", ["Right" => "You walk into the forest..", "Left" => "You walk along the path.."]);
