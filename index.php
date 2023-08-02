<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-type: application/json');
if (!empty($_POST['subdomain']))
    $subdomain = $_POST['subdomain'];
else
    $subdomain = (substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 6));

$branch = null;
if (!empty($_POST['branch']))
    $branch = $_POST['branch'];


if (!empty($_POST['action']))
    $action = $_POST['action'];
else
    $action = 'mon';

$port = findFreePort();
$initialDirPath = '/home/dockeruser/microservice/testPlatform/initializer/';
$projectsDefaultPath = '/home/dockeruser/microservice/testPlatform/volumes/';
$envFilePath = '/home/dockeruser/microservice/testPlatform/initializer/.env';
$nginxConfigTemplatePath = "/var/www/html/testPlatformHandler/nginx_config_template.conf";
$domain = "domain.local";
$successResponse = "\n"; //file_get_contents('./index.html');

actionHandler($action, $initialDirPath, $successResponse, $projectsDefaultPath, $domain, $subdomain, $port, $nginxConfigTemplatePath, $branch);


function findFreePort($host = '127.0.0.1', $start = 8000, $end = 9000)
{
    foreach (range($start, $end) as $port) {
        $connection = @fsockopen($host, $port);
        if (is_resource($connection))
            fclose($connection);
        else
            return $port;
    }
    return false;
}

function updateEnvFile($initialDirPath, $subdomain, $port)
{
    $return = [];
    exec("sudo mkdir -p " . $initialDirPath . $subdomain, $result);
    array_push($return, ["sudo mkdir -p " . $initialDirPath . $subdomain, $result]);
    $result = null;

    exec("sudo chmod 777 -R " . $initialDirPath . $subdomain, $result);
    array_push($return, ["sudo chmod 777 -R " . $initialDirPath . $subdomain, $result]);
    $result = null;

    exec("sudo cp " . $initialDirPath . ".env" . " " . $initialDirPath . $subdomain . "/", $result);
    array_push($return, ["sudo cp " . $initialDirPath . ".env" . " " . $initialDirPath . $subdomain . "/", $result]);
    $result = null;

    exec("sudo cp " . $initialDirPath . "docker-compose.yml" . " " . $initialDirPath . $subdomain . "/", $result);
    array_push($return, ["sudo cp " . $initialDirPath . "docker-compose.yml" . " " . $initialDirPath . $subdomain . "/", $result]);
    $result = null;

    exec("sudo chmod 777 -R " . $initialDirPath . $subdomain, $result);
    array_push($return, ["sudo chmod 777 -R " . $initialDirPath . $subdomain, $result]);
    $result = null;

    $env = fopen($initialDirPath . $subdomain . "/.env", 'w') or die('Unable to open env file');
    $str = "COMPOSE_PROJECT_NAME=project-$subdomain\nTASK_ID=$subdomain\nPORT=$port";
    fwrite($env, $str);
    fclose($env);
    return $return;
}

function getColor($num) {
    $hash = md5('color' . $num); // modify 'color' to get a different palette
    return array(
        hexdec(substr($hash, 0, 2)), // r
        hexdec(substr($hash, 2, 2)), // g
        hexdec(substr($hash, 4, 2))); //b
}


function actionHandler($action, $initialPath, $successResponse, $projectsPath, $domain, $subdomain, $port, $nginxConfigTemplatePath, $branch = null)
{
    $return = [];
    switch ($action) {
        case 'mon':
        {
            header('Content-type: text/html');
//            $temp = getColor(rand(0,255));
//            $color = "rgb(".$temp[0].",".$temp[1].",".$temp[2].")";
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
                      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
                      crossorigin="anonymous">
                <title>Test Platform Handler</title>
                <style>
                    .btn-outline-custom:hover {
                        color: #fff;
                        background-color: rgb(61,191,20);
                        border-color: rgb(61,191,20);
                    }
                    .btn-outline-custom {
                        color: rgb(61,191,20);
                        border-color: rgb(61,191,20);
                    }
                </style>
            </head>
            <body class="bg-dark">
            <div class="container">
                <div class="row">
                    <div class="table-responsive col-12">
                        <table class="table table-dark table-striped table-hover table-sm align-middle">
                            <thead>
                            <tr>
                                <th class="text-center" scope="col">#</th>
                                <th class="text-center" scope="col">CONTAINER NAME</th>
                                <th class="text-center" scope="col">TASK ID</th>
                                <th class="text-center" scope="col">DOMAIN</th>
                                <th class="text-center" scope="col">CLICKUP</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            exec("sudo docker ps --format '{{.Names}} ({{.Ports}})'", $o, $ret);
                            foreach ($o as $key => $container) {
                                $taskID = explode("-", explode(" ", $container)[0])[1];
                                $key+=1;
                                echo "<tr>
                                <th class=\"text-center\" scope=\"row\" >$key</th>
                                <th class=\"text-center\">$container</th>
                                <th class=\"text-center\">#$taskID</th>
                                <th class=\"text-center\"><a class=\"btn btn-outline-custom\" href=\"http://$taskID.domain.local\" target=\"_blank\"</a> OPEN LINK </th>
                                <th class=\"text-center\"><a class=\"btn btn-outline-warning\" href=\"https://taskmanager.com/$taskID\" target=\"_blank\"</a> OPEN LINK </th>";
                            }
                            echo "</tr>";
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
                    integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
                    crossorigin="anonymous"></script>
            </body>
            </html>
            <?php
            break;
        }
        case 'create':
        {
            array_push($return, updateEnvFile($initialPath, $subdomain, $port));
            if (!empty($branch)) {
                exec("sudo git -C '" . $projectsPath . "#common/app/' pull", $result);
                array_push($return, ["sudo git -C '" . $projectsPath . "#common/app/' pull", $result]);
                $result = null;

                exec("sudo mkdir " . $projectsPath . $subdomain, $result);
                array_push($return, ["sudo mkdir " . $projectsPath . $subdomain, $result]);
                $result = null;

                exec("sudo cp -rp " . $projectsPath . "#common/app/." . " " . $projectsPath . $subdomain . "/", $result);
                array_push($return, ["sudo cp -rp " . $projectsPath . "#common/app/." . " " . $projectsPath . $subdomain . "/", $result]);
                $result = null;

                exec("sudo git -C '" . $projectsPath . $subdomain . "' add .", $result);
                array_push($return, ["sudo git -C '" . $projectsPath . $subdomain . "' add .", $result]);
                $result = null;

                exec("sudo git -C '" . $projectsPath . $subdomain . "' reset --hard origin/develop", $result);
                array_push($return, ["sudo git -C '" . $projectsPath . $subdomain . "' reset --hard origin/develop", $result]);
                $result = null;

                exec("sudo git -C '" . $projectsPath . $subdomain . "' pull --all", $result);
                array_push($return, ["sudo git -C '" . $projectsPath . $subdomain . "' pull --all", $result]);
                $result = null;

                exec("sudo git -C '" . $projectsPath . $subdomain . "' checkout '$branch'", $result);
                array_push($return, ["sudo git -C '" . $projectsPath . $subdomain . "' checkout '$branch'", $result]);
                $result = null;

                exec("sudo mkdir " . $projectsPath . $subdomain . "/storage/logs", $result);
                array_push($return, ["sudo mkdir " . $projectsPath . $subdomain . "/storage/logs", $result]);
                $result = null;

                exec("sudo chmod 777 -R " . $projectsPath . $subdomain . "/storage", $result);
                array_push($return, ["sudo chmod 777 -R " . $projectsPath . $subdomain . "/storage", $result]);
                $result = null;

                exec("sudo mv " . $projectsPath . $subdomain . "/config/websites/127.0.0.1.yaml " . $projectsPath . $subdomain . "/config/websites/" . $subdomain . "." . $domain . ".yaml", $result);
                array_push($return, ["sudo mv " . $projectsPath . $subdomain . "/config/websites/127.0.0.1.yaml " . $projectsPath . $subdomain . "/config/websites/" . $subdomain . "." . $domain . ".yaml", $result]);
                $result = null;

                exec("sudo docker-compose -f " . $initialPath . $subdomain . "/docker-compose.yml up -d | sed -u 's/^[^|]*[^ ]* //'", $result);
                array_push($return, ["sudo docker-compose -f " . $initialPath . $subdomain . "/docker-compose.yml up -d | sed -u 's/^[^|]*[^ ]* //'", $result]);
                $result = null;

                exec("sudo docker exec -d project-$subdomain /usr/bin/php composer.phar update --no-interaction", $result);
                array_push($return, ["sudo docker exec -d project-$subdomain /usr/bin/php composer.phar update --no-interaction", $result]);
                $result = null;


                exec("sudo cp " . $nginxConfigTemplatePath . " " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result);
                array_push($return, ["sudo cp " . $nginxConfigTemplatePath . " " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result]);
                $result = null;

                exec("sudo sed -i 's/SERVERNAME/" . $subdomain . "." . $domain . "/' " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result);
                array_push($return, ["sudo sed -i 's/SERVERNAME/" . $subdomain . "." . $domain . "/' " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result]);
                $result = null;

                exec("sudo sed -i 's/PORT/" . $port . "/' " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result);
                array_push($return, ["sudo sed -i 's/PORT/" . $port . "/' " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf", $result]);
                $result = null;

                exec("sudo mv " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf /etc/nginx/conf.d/", $result);
                array_push($return, ["sudo mv " . $projectsPath . "#common/" . $subdomain . "." . $domain . ".conf /etc/nginx/conf.d/", $result]);
                $result = null;

                exec("sudo nginx -s reload", $result);
                array_push($return, ["sudo nginx -s reload", $result]);
                $result = null;

            }

            echo $successResponse;
            break;
        }
        case 'update':
        {
            exec("sudo docker exec -i project-$subdomain /bin/pwd", $result);
            array_push($return, ["sudo docker exec -i project-$subdomain /bin/pwd", $result]);
            $result = null;

            exec("sudo docker exec -i project-$subdomain /usr/bin/git pull", $result);
            array_push($return, ["sudo docker exec -i project-$subdomain /usr/bin/git pull", $result]);
            $result = null;

            exec("sudo docker exec -i project-$subdomain /usr/bin/php composer.phar update --no-interaction", $result);
            array_push($return, ["sudo docker exec -i project-$subdomain /usr/bin/php composer.phar update --no-interaction", $result]);
            $result = null;

            echo $successResponse;
            break;
        }
        case 'delete':
        {
            exec("sudo docker-compose -f " . $initialPath . $subdomain . "/docker-compose.yml down | sed -u 's/^[^|]*[^ ]* //'", $result);
            array_push($return, ["sudo docker-compose -f " . $initialPath . $subdomain . "/docker-compose.yml down | sed -u 's/^[^|]*[^ ]* //'", $result]);
            $result = null;

            exec("sudo rm -rf " . $projectsPath . $subdomain, $result);
            array_push($return, ["sudo rm -rf " . $projectsPath . $subdomain, $result]);
            $result = null;

            exec("sudo rm -rf " . $initialPath . $subdomain, $result);
            array_push($return, ["sudo rm -rf " . $initialPath . $subdomain, $result]);
            $result = null;

            exec("sudo rm -rf /etc/nginx/conf.d/" . $subdomain . "." . $domain . ".conf", $result);
            array_push($return, ["sudo rm -rf /etc/nginx/conf.d/" . $subdomain . "." . $domain . ".conf", $result]);
            $result = null;

            exec("sudo nginx -s reload", $result);
            array_push($return, ["sudo nginx -s reload", $result]);
            $result = null;

            echo $successResponse;
            break;
        }
        default:
            echo "specified action is not defined";
            die();

    }
    if (sizeof($return) > 0)
        echo json_encode($return);
}
