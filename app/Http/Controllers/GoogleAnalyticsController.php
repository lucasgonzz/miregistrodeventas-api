<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleAnalyticsController extends Controller
{
    public function getAnalyticsSummary(){
        // $from_date = date(“Y-m-d”, strtotime($request->get(‘from_date‘,”7 days ago”)));
        // $to_date = date(“Y-m-d”,strtotime($request->get(‘to_date’,$request->get(‘from_date’,’today’))));
        $from_date = \Carbon\Carbon::now()->subWeek();
        $to_date = \Carbon\Carbon::now();
        $gAData = $this->gASummary($from_date,$to_date);
        return $gAData; 
    }

    //to get the summary of google analytics.
    private function gASummary($date_from,$date_to) {
        $service_account_email = 'analytics-kioscoverde-en-mireg@kiosco-verde.iam.gserviceaccount.com';
        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName('kiosco-verde');
        $analytics = new \Google_Service_Analytics($client);
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_email,
            array(\Google_Service_Analytics::ANALYTICS_READONLY),
            'cf780fa749cb7761859ffe524a2e068a67247bb7'
        );
        $client->setAssertionCredentials($cred);
        if($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }
        $optParams = [
            'dimensions' => 'ga:date',
            'sort'       => '-ga:date'
        ];
        $results = $analytics->data_ga->get(
            'ga:107348674119305753446',
            $date_from,
            $date_to,
            'ga:sessions,ga:users,ga:pageviews,ga:bounceRate,ga:hits,ga:avgSessionDuration',
            $optParams
        );
        $rows = $results->getRows();
        $rows_re_align = [];
        foreach($rows as $key => $row) {
            foreach($row as $k => $d) {
                $rows_re_align[$k][$key] = $d;
            }
        }
        $optParams = array(
            'dimensions' => 'rt:medium'
        );
        try {
            $results1 = $analytics->data_realtime->get(
                'ga:107348674119305753446',
                'rt:activeUsers',
                $optParams
            );
            // Success.
        } catch (apiServiceException $e) {
            // Handle API service exceptions.
            $error = $e->getMessage();
        }
        $active_users = $results1->totalsForAllResults ;
        return [
            'data'          => $rows_re_align ,
            'summary'       => $results->getTotalsForAllResults(),
            'active_users'  => $active_users['rt:activeUsers']
        ];
    }
}
