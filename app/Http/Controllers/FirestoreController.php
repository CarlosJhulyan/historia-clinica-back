<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use \Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use App\Core\CustomResponse;
use Illuminate\Support\Facades\Validator;

class FirestoreController extends Controller
{
    public function syncFirestore(Request $request)
    {
        $factory = (new Factory)->withServiceAccount(__DIR__ . '/telemedicinacallme-b89db3a17be2.json');

        $firestore = new FirestoreClient([
            'projectId' => 'telemedicinacallme',
        ]);

        $data = [
            "doctor_id"  => intval($request->get('doctorid')),
            "doctor_name"  => $request->get('doctorname'),
            "doctor_photo_url"  => "",
            "id"  =>   intval($request->get('queryid')),
            "pacient_id"  =>  intval($request->get('patientid')),
            "pacient_name" => $request->get('patientname'),
            "pacient_photo_url"  => "",
            "doctor_attended" => false,
            "pacient_attended" => false,
            "started" => false
        ];

        $addedDocRef = $firestore->collection('videollamadas')->document($request->get('queryid'))->set($data);
    }

    public function syncDoctor(Request $request)
    {

        $validator = Validator::make($request->all(), [
			'queryid' => 'required',
			'token' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

        $factory = (new Factory)->withServiceAccount(__DIR__ . '/telemedicinacallme-b89db3a17be2.json');

        $firestore = new FirestoreClient([
            'projectId' => 'telemedicinacallme',
        ]);

        $addedDocRef = $firestore->collection('videollamadas')
                                ->document($request->get('queryid'))
                                ->update([['path' => 'started', 'value' => true],
                                        ['path' => 'pacient_attended', 'value' => false]]);
        $headers = [
            'Authorization:key=AAAAor8RoT0:APA91bEjIVLN8tvWBDlJppY76Z0NWbuJX2F06Gc8wOnczCU61zNBAUVioTolwbdYHMbIIMAy9_3HkCMDBetXuCph2sFgdJwRnelg3uKitDGBWv3xCQJBhN2_quXTD8ijHym_ng_CGztp',
            'Content-Type: application/json'
        ];
        $url = 'https://fcm.googleapis.com/fcm/send';

        $ch = curl_init($url);
        $data = array(
                'data' => array(
                                    'open' => 1,
                                ),
            'to' => $request->get('token')
                                
        );
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch); 

        return CustomResponse::success('Procedimiento satisfactorio');

    }

    public function syncPatient(Request $request)
    {

        $validator = Validator::make($request->all(), [
			'queryid' => 'required',
			'token' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

        $factory = (new Factory)->withServiceAccount(__DIR__ . '/telemedicinacallme-b89db3a17be2.json');

        $firestore = new FirestoreClient([
            'projectId' => 'telemedicinacallme',
        ]);

        $addedDocRef = $firestore->collection('videollamadas')
                                ->document($request->get('queryid'))
                                ->update([['path' => 'started', 'value' => true],
                                        ['path' => 'doctor_attended', 'value' => false]]);
        $headers = [
            'Authorization:key=AAAAor8RoT0:APA91bEjIVLN8tvWBDlJppY76Z0NWbuJX2F06Gc8wOnczCU61zNBAUVioTolwbdYHMbIIMAy9_3HkCMDBetXuCph2sFgdJwRnelg3uKitDGBWv3xCQJBhN2_quXTD8ijHym_ng_CGztp',
            'Content-Type: application/json'
        ];
        $url = 'https://fcm.googleapis.com/fcm/send';

        $ch = curl_init($url);
        $data = array(
                'data' => array(
                                    'open' => 1,
                                ),
            'to' => $request->get('token')
                                
        );
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch); 

        return CustomResponse::success('Procedimiento satisfactorio');
    }
}




