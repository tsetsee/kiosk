<?php
/**
 * Created by PhpStorm.
 * User: tsetsee
 * Date: 9/13/17
 * Time: 4:23 PM
 */

use \Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/API/STGW/STGW.php';

$app->get('/test', function () use ($app) {
    $stgw = new \API\STGW\STGW($app['monolog']);
    $response = $stgw->giveICTDataPackage('94094096', 'PRE_3DAY_1.5GB');

    var_dump($response);
    return "";
});

$app->get('/generate', function (Request $request) use ($app) {
    /**@var PDOStatement $stmt */
    $stmt = $app['db']->prepare(<<<SQL
      DELETE FROM prize;
      ALTER TABLE prize AUTO_INCREMENT = 1;
      DELETE FROM superprize;
      ALTER TABLE superprize AUTO_INCREMENT = 1
SQL
    );

    $stmt->execute();

    $timeTable = array(
        'kiosk1' => array(
            // 15
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 12:25:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 13:40:00'),
            ),
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 17:15:00'),
            ),
            // 16
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 12:55:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 14:05:00'),
            ),
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 17:00:00'),
            ),
            // 17
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 11:00:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 13:50:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 15:40:00'),
            ),
        ),
        'kiosk2' => array(
            // 15
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 13:10:00'),
            ),
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 15:05:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-15 16:35:00'),
            ),
            // 16
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 11:35:00'),
            ),
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 15:10:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-16 16:00:00'),
            ),
            // 17
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 11:55:00'),
            ),
            array(
                'name' => 'wifi',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 12:45:00'),
            ),
            array(
                'name' => 'lg',
                'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-17 14:30:00'),
            ),
        ),
    );

    $badget = array(
        '14' => array(
            'data1' => 300,
            'cardcase' => 150,
            'data3' => 75,
            'data30' => 50,
        ),
        '15' => array(
            'data1' => 300,
            'cardcase' => 150,
            'data3' => 75,
            'data30' => 50,
        ),
        '16' => array(
            'data1' => 400,
            'cardcase' => 200,
            'data3' => 100,
            'data30' => 50,
        ),
        '17' => array(
            'data1' => 300,
            'cardcase' => 150,
            'data3' => 75,
            'data30' => 50,
        ),
    );

    $prizeList = array(
        '14' => array(),
        '15' => array(),
        '16' => array(),
        '17' => array(),
    );

    foreach($badget as $day => $prizes) {
        foreach($prizes as $name => $too) {
            for($i = 1; $i <= $too; $i++) {
                $prizeList[$day][] = array(
                    'name' => $name,
                    'time' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-09-'.$day.' 06:00:00')
                );
            }
        }
        shuffle($prizeList[$day]);
    }

    $kiosk = $request->get('kiosk');



    foreach($timeTable['kiosk'.$kiosk] as $row) {
        $stmt = $app['db']->prepare(<<<SQL
      INSERT INTO superprize(name,active_at,status) VALUES(:name, :time, :status)
SQL
        );
        $stmt->bindValue('name', $row['name']);
        $stmt->bindValue('time', $row['time'], 'datetime');
        $stmt->bindValue('status', 'ready');

        $stmt->execute();
    }


    foreach($prizeList as $dayPrize) {
        foreach($dayPrize as $row) {
            $stmt = $app['db']->prepare(<<<SQL
      INSERT INTO prize(name,active_at,status) VALUES(:name, :time, :status)
SQL
            );
            $stmt->bindValue('name', $row['name']);
            $stmt->bindValue('time', $row['time'], 'datetime');
            $stmt->bindValue('status', 'ready');

            $stmt->execute();
        }
    }


    return "ok";
});

$app->post('/getPrizeCount', function () use ($app) {
    $arr = array(
        'status' => 'OK',
        'count' => null,
    );

    $now = new \DateTime();

    $sdate = clone $now;
    $sdate->setTime(0, 0, 0);

    $edate = clone $sdate;
    $edate->modify('+1 day');

    $stmt = $app['db']->prepare(<<<SQL
      SELECT COUNT(id) as prizeleft FROM prize 
      WHERE
        used_at is null 
        AND active_at >= :sdate
        AND active_at < :edate
SQL
    );
    $stmt->bindValue('sdate', $sdate, 'datetime');
    $stmt->bindValue('edate', $edate, 'datetime');

    $stmt->execute();

    $count = $stmt->fetchAll()[0]['prizeleft'];

    $arr['count'] = $count;

    return $app->json($arr);
});

$app->post('/getPrize', function (Request $request) use ($app) {
    $arr = array(
        'status' => 'OK',
        'name' => null
    );

    try {
        $msisdn = $request->get('msisdn');
        preg_match('/^(99|95|94|85)\d{6}$/', $msisdn, $matches);

        if (count($matches) === 0) {
            $arr['message'] = 'Мобикомын дугаар оруулна уу.';
            throw new \Exception();
        }

        $now = new \DateTime();

        $sdate = clone $now;
        $sdate->setTime(0, 0, 0);

        $edate = clone $sdate;
        $edate->modify('+1 day');

        /**@var PDOStatement $stmt */

        $stmt = $app['db']->prepare(<<<SQL
        SELECT * from superprize
        WHERE
            used_at is null 
            AND active_at >= :sdate
            AND active_at <= :now
        LIMIT 1
SQL
        );

        $stmt->bindValue('sdate', $sdate, 'datetime');
        $stmt->bindValue('now', $now, 'datetime');

        $stmt->execute();

        $superPrize = $stmt->fetchAll();

        if (count($superPrize) > 0) {
            $row = $superPrize[0];

            $stmt = $app['db']->prepare(<<<SQL
              UPDATE superprize
              SET winner_isdn=:isdn, used_at=:now, status='give'
              WHERE id=:id
SQL
            );

            $stmt->bindValue('isdn', $msisdn);
            $stmt->bindValue('now', $now, 'datetime');
            $stmt->bindValue('id', $row['id']);

            $stmt->execute();

            $arr['name'] = $row['name'];
            $arr['id'] = $row['id'];
            $arr['isSuper'] = true;
        } else {

            $stmt = $app['db']->prepare(<<<SQL
      SELECT * FROM prize 
      WHERE
        used_at is null 
        AND active_at >= :sdate
        AND active_at < :edate
      LIMIT 1
SQL
            );
            $stmt->bindValue('sdate', $sdate, 'datetime');
            $stmt->bindValue('edate', $edate, 'datetime');

            $stmt->execute();

            $prizes = $stmt->fetchAll();

            if (count($prizes) === 0) {
                $arr['message'] = 'Бэлэг дууссан байна.';
                throw new \Exception();
            }

            $row = $prizes[0];

            $stmt = $app['db']->prepare(<<<SQL
              UPDATE prize 
              SET winner_isdn=:isdn, used_at=:now, status='give'
              WHERE id=:id
SQL
            );

            $stmt->bindValue('isdn', $msisdn);
            $stmt->bindValue('now', $now, 'datetime');
            $stmt->bindValue('id', $row['id']);

            $stmt->execute();

            $arr['name'] = $row['name'];
            $arr['id'] = $row['id'];
            $arr['isSuper'] = true;
        }
    } catch (\Exception $e) {
        $arr['status'] = 'fail';
    }

    return $app->json($arr);
});


$app->post('/checkoutPrize', function (Request $request) use ($app) {
    $id = $request->get('id');
    $isSuper = $request->get('isSuper');
    $status = 'ok';

    $tableName = $isSuper ? 'superprize' : 'prize';

    /**@var PDOStatement $stmt */
    $stmt = $app['db']->prepare(<<<SQL
      SELECT * FROM $tableName 
      WHERE
        id=:id
      LIMIT 1
SQL
    );
    $stmt->bindValue('id', $id);

    $stmt->execute();

    $prizes = $stmt->fetchAll();

    if(count($prizes) > 0) {
        $row = $prizes[0];
        $msisdn = $row['winner_isdn'];

        $packageId = null;
        switch($row['name']) {
            case 'data1':
                $packageId = 'PRE_1DAY_600MB';
                break;
            case 'data3':
                $packageId = 'PRE_3DAY_1.5GB';
                break;
            case 'data30':
                $packageId = 'UNLIMITED_30DAY';
                break;
        }

        if($packageId) {
            $stgw = new \API\STGW\STGW($app['monolog']);
            $response = $stgw->giveICTDataPackage($msisdn, $packageId);
        }

        $stmt = $app['db']->prepare(<<<SQL
      UPDATE $tableName 
      SET status=:status
      WHERE
        id=:id
SQL
        );
        $stmt->bindValue('id', $id);
        $stmt->bindValue('status', $status);

        $stmt->execute();
    }

    return $status;
});