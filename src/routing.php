<?php
/**
 * Created by PhpStorm.
 * User: tsetsee
 * Date: 9/13/17
 * Time: 4:23 PM
 */
use \Symfony\Component\HttpFoundation\Request;

$app->get('/hi',function() use($app) {
    $kiosks = $app['db']->fetchAll('SELECT * FROM prize');

    var_dump($kiosks);
    return 'Hello ';
});

$app->post('/getPrizeCount', function() use($app) {
    $arr = array(
        'status' => 'OK',
        'count' => null,
    );

    $now = new \DateTime();

    $sdate = clone $now;
    $sdate->setTime(0,0,0);

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

$app->post('/getPrize', function(Request $request) use($app) {
    $arr = array(
        'status' => 'OK',
        'name' => null
    );

    try {
        $msisdn = $request->get('msisdn');
        preg_match('/^(99|95|94|85)\d{6}$/', $msisdn, $matches);

        if (count($matches) === 0) {
            throw new \Exception();
        }

        $now = new \DateTime();

        $sdate = clone $now;
        $sdate->setTime(0,0,0);

        $edate = clone $sdate;
        $edate->modify('+1 day');

        /**@var PDOStatement $stmt*/

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

        if(count($superPrize) > 0) {
            $row = $superPrize[0];

            $stmt = $app['db']->prepare(<<<SQL
              UPDATE superprize
              SET winner_isdn=:isdn, used_at=:now
              WHERE id=:id
SQL
            );

            $stmt->bindValue('isdn', $msisdn);
            $stmt->bindValue('now', $now, 'datetime');
            $stmt->bindValue('id', $row['id']);

            $stmt->execute();

            $arr['name'] = $row['name'];
        }
        else {

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

            if(count($prizes) === 0) {
                throw new \Exception();
            }

            $row = $prizes[0];

            $stmt = $app['db']->prepare(<<<SQL
              UPDATE prize 
              SET winner_isdn=:isdn, used_at=:now 
              WHERE id=:id
SQL
            );

            $stmt->bindValue('isdn', $msisdn);
            $stmt->bindValue('now', $now, 'datetime');
            $stmt->bindValue('id', $row['id']);

            $stmt->execute();

            $arr['name'] = $row['name'];
        }
    }
    catch(\Exception $e) {
        $arr = array(
            'status' => 'fail',
        );
    }

    return $app->json($arr);
});