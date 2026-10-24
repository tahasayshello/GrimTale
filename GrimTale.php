<?php
define("DEBUG", true); // skip dialogue

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

if (DEBUG == true) {
    $STORY = "DEBUG";
}
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

$LORDCHAIN = [
    "ATK" => 50,
    "HP" => 5000
];

$SP = 0; // SP stands for save point, this is how game progress will be handled
$SHARD = false; // becomes true when the OPAQUE SHARD is picked up

function Write(string $STORY, bool $SKIP)
{
    if ($SKIP === true) {
        echo $STORY . PHP_EOL; // instant echo
        return;
    }

    $keys = 0;
    while (true) {
        $ch = @$STORY[$keys]; // @ to ignore string warnings
        if ($ch == "") {
            echo PHP_EOL;
            break;
        }
        echo $ch;
        if ($ch == "." || $ch == "," || $ch == "?" || $ch == "!") {
            sleep(1);
        } else {
            usleep(20000);
        }
        $keys++;
    }
}

function SlowText(string $STORY)
{
    $keys = 0;
    while (true) {
        $ch = @$STORY[$keys]; // @ to ignore string warnings
        if ($ch == "") {
            echo PHP_EOL;
            break;
        }
        echo $ch;
        if ($ch == "." || $ch == ",") {
            sleep(2);
        } else {
            usleep(90000);
        }
        $keys++;
    }
}

function ClearScreen()
{
    echo "\e[H\e[J"; // this command clears the terminal
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
            Write("You chose $prompt, $RESULT", false);
            return $OPTION; // gives back the option if called as a variable
        }
    }

    // only reached if no option matched
    ClearScreen();
    // echo "Invalid answer" . PHP_EOL;
    return Scenario($TEXT, $OPTIONS); // runs the function again after invalid input
}

// battle system, every turn the player chooses ATK or DEF
// ATK = hurt the enemy but you take the full hit
// DEF = you only take 1 dmg if you keep defending the enemy gets exhausted and stops fighting
// returns "WON" "DEAD" or "EXHAUSTED"
function Battle(array $FOE, string $FOENAME, bool $SHOWATK)
{
    global $PLR;
    $DEFS = 0; // how many times in a row the player defended

    Write("A $FOENAME appears!", false);

    while (true) {
        if ($SHOWATK == true) {
            Write("$FOENAME | HP: " . $FOE["HP"] . " | ATK: " . $FOE["ATK"], true);
        } else {
            Write("$FOENAME | HP: " . $FOE["HP"] . " | ATK: ???", true); // for the boss
        }
        Write("You | HP: " . $PLR["HP"] . " | ATK: " . $PLR["ATK"], true);

        $ACTION = Scenario("What will you do?", ["ATK" => "You attack the $FOENAME!", "DEF" => "You raise your guard.."]);

        if ($ACTION == "ATK") {
            $FOE["HP"] -= $PLR["ATK"];
            $DEFS = 0;
            $DMG = $FOE["ATK"];
        } else {
            $DEFS++;
            $DMG = 1;
        }

        if ($FOE["HP"] <= 0) { // if the enemy is defeated
            return "WON";
        }

        if ($DEFS >= 5) { // whenever you defend 5 or more times the enemy becomes exhausted
            return "EXHAUSTED";
        }

        $PLR["HP"] -= $DMG;
        Write("The $FOENAME hits you for $DMG dmg.", true);

        if ($PLR["HP"] <= 0) { // player death
            return "DEAD";
        }
    }
}

Write($STORY, false);
ClearScreen();

// first save point
$SP = 1;
$SAVEPLR = $PLR;
$SAVESHARD = $SHARD;
// overwriting the data (save)
$PLAYING = true;
$DEAD = false;

while ($PLAYING == true) {

    // SP 1
    if ($SP == 1 && $DEAD == false) {
        ClearScreen();

        $WAY = Scenario("You find yourself in an open-area, there are 2 paths, which path will you take?", ["Right" => "You walk into the forest..", "Left" => "You walk along the path.."]);

        if ($WAY == "Right") {
            // forest (OPAQUE SHARD)
            Write("While walking through the forest, you come across a strange piece of glass..", false);
            $PICK = Scenario("Do you pick up the piece of glass?", ["Yes" => "You pick it up..", "No" => "You leave it where it lies.."]);

            if ($PICK == "Yes") {
                $SHARD = true;
                Write("You have obtained the item: OPAQUE SHARD!", true);
            }

            Write("You keep walking through the forest..", false);
        } else {
            // path with enemies (goblin slime)
            $RESULT = Battle($GOBLIN, "Goblin", true);

            if ($RESULT == "DEAD") {
                $DEAD = true;
            }

            if ($RESULT == "WON") {
                Write("You defeated the Goblin! You gained experience points and became stronger!", false);
                $PLR["LVL"] += 1;
                $PLR["ATK"] += 10;
            }

            if ($RESULT == "EXHAUSTED") {
                Write("The Goblin is too tired to keep fighting and runs away..", false);
            }

            if ($DEAD == false) {
                Write("You continue along the path, until something wobbles in front of you..", false);

                $RESULT = Battle($SLIME, "Slime", true);

                if ($RESULT == "DEAD") {
                    $DEAD = true;
                }

                if ($RESULT == "WON") {
                    Write("You defeated the Slime! It dropped nothing..", false);
                }

                if ($RESULT == "EXHAUSTED") {
                    Write("The Slime lost interest in you and wobbles away..", false);
                }
            }
        }

        if ($DEAD == false) {
            // next save point
            $SP = 2;
            $PLR["HP"] = 100;
            $SAVEPLR = $PLR;
            $SAVESHARD = $SHARD;
        }
    }

    // SP 2
    if ($SP == 2 && $DEAD == false) {
        ClearScreen();

        Write("Before you stands a gigantic, grim castle. This must be the home of Lord Chain..", false);

        $ENTRY = Scenario("Do you walk in through the front door, or try another route?", ["Front" => "You walk up to the front door..", "Other" => "You search for another way in.."]);

        if ($ENTRY == "Front") {
            Write("The security system of the castle detects you and shoots arrows at you, you fall to the ground..", false);
            $DEAD = true;
        } else {
            Write("You climb into the castle through a window..", false);
            Write("After a while of walking through the castle, you come across 2 doors.", false);

            $DOOR = Scenario("Which door will you choose? (the left door or the right one)", ["Left" => "You open the left door..", "Right" => "You open the right door.."]);

            if ($DOOR == "Right") {
                Write("The floor gives way beneath you, you fall into a hole with spikes on the bottom..", false);
                $DEAD = true;
            } else {
                // next save point
                $SP = 3;
                $PLR["HP"] = 100;
                $SAVEPLR = $PLR;
                $SAVESHARD = $SHARD;
            }
        }
    }

    //  SP 3
    if ($SP == 3 && $DEAD == false) {
        ClearScreen();

        Write("You enter a room with 5 doors. The first door is a bright blue colour, the 2nd and 3rd door are normally coloured, the 4th door has no door handle and the last door stands wide open.", false);

        $INROOM = true;

        while ($INROOM == true && $DEAD == false) {
            $CHOSEN = Scenario("Which door will you go to? (1 - 5)", ["1" => "You walk to the first door..", "2" => "You walk to the second door..", "3" => "You walk to the third door..", "4" => "You walk to the fourth door..", "5" => "You walk to the last door.."]);

            if ($CHOSEN == 1) {
                $ACT = Scenario("Do you open the door or go back?", ["Open" => "You open the door..", "Back" => "You walk back to the middle of the room.."]);

                if ($ACT == "Open") {
                    Write("You walk further inside, into the dark.. Suddenly a flame ignites in the middle of the room, the whole room is set ablaze and you burn alive..", false);
                    $DEAD = true;
                }
            }

            if ($CHOSEN == 2 || $CHOSEN == 3) {
                $ACT = Scenario("Do you open the door or go back?", ["Open" => "You try to turn the door handle..", "Back" => "You walk back to the middle of the room.."]);

                if ($ACT == "Open") {
                    Write("Clink, clink, clink.. it is locked. You walk back.", false);
                }
            }

            if ($CHOSEN == 4) {
                Write("This door has no door handle, there is no way to open it. You walk back.", false);
            }

            if ($CHOSEN == 5) {
                $ACT = Scenario("Do you walk inside or go back?", ["Open" => "You walk through the open door..", "Back" => "You walk back to the middle of the room.."]);

                if ($ACT == "Open") {
                    Write("You walk inside, and find yourself in a large hall with a red carpet in the middle, leading to a massive double door.", false);

                    $DD = Scenario("Do you open the double door?", ["Y" => "You push the double door open..", "N" => "You decide to stay in the hall.."]);

                    if ($DD == "N") {
                        Write("A guard walks into the hall, and you are impaled by the spear of the guard..", false);
                        $DEAD = true;
                    } else {
                        $INROOM = false;
                        // next save point
                        $SP = 4;
                        $PLR["HP"] = 100;
                        $SAVEPLR = $PLR;
                        $SAVESHARD = $SHARD;
                    }
                }
            }
        }
    }

    // SP 4
    if ($SP == 4 && $DEAD == false) {
        ClearScreen();

        Write("You are in another hall. In the distance you see a dark figure, he is standing at the end of the hall.", false);

        $FIGURE = Scenario("What do you do?", ["Walk" => "You keep walking towards the figure..", "Wait" => "You stand still and wait.."]);

        if ($FIGURE == "Walk") {
            Write("The figure draws a long sword and chops your head off..", false);
            $DEAD = true;
        } else {
            Write("The figure steps into the light and introduces himself as the evil warlord.. LORD CHAIN!", false);

            if ($SHARD == true) {
                Write("The OPAQUE SHARD in your bag starts to glow, you feel an enormous power flowing through you!", false);
                $PLR["ATK"] += 2500;
            }

            $RESULT = Battle($LORDCHAIN, "Lord Chain", false);

            if ($RESULT == "DEAD") {
                Write("Lord Chain strikes you down..", false);
                $DEAD = true;
            }

            // GOOD ENDING
            if ($RESULT == "EXHAUSTED") {
                ClearScreen();
                Write("Lord Chain is out of breath, he can't hit you anymore and lowers his weapon.", false);
                Write("Your defence has made him realise how much he has lost himself, and says: 'You are right, hero, I have no more reason to fight..'", false);
                Write("He leaves the continent in peace, and light returns to Grasstonia. $NAME is remembered as the saviour of the land. (GOOD ENDING)", false);
                $PLAYING = false;
            }

            // NEUTRAL ENDING
            if ($RESULT == "WON") {
                ClearScreen();
                Write("Lord Chain is brought to his knees, he looks at you and speaks his last words:", false);
                Write("'It seems i have been defeated, before i leave this world.. i have one more request..'", false);
                ClearScreen();
                SlowText("LET FATE TOY WITH YOU LIKE A FOOL.");
                Write("He falls to the ground and does not move again. You may have won the battle, but was it worth the blood on your hands..? (NEUTRAL ENDING)", false);
                $PLAYING = false;
            }
        }
    }

    // death screen
    if ($DEAD == true) {
        ClearScreen();

        Write("You have died, unfortunately.. your journey ends here.", false);

        $AGAIN = Scenario("Start again from the last save point, or give up?", ["Retry" => "You start to slowly get up..", "Quit" => "Then so shall be done.."]);

        if ($AGAIN == "Retry") {
            // go back to the stats from the last save point
            $PLR = $SAVEPLR;
            $SHARD = $SAVESHARD;
            $DEAD = false;
        } else {
            Write("Closing game..", false);
            ClearScreen();
            exit;
        }
    }
}

Write("THE END", false);