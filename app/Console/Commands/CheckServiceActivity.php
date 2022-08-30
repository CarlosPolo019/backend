<?php

namespace DreamFactory\Console\Commands;

use Illuminate\Console\Command;
use DB;

class CheckServiceActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servicio:check-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $servicios = DB::table('airlink_u.servicio_sin_finalizar')
	       ->select('id','id_conductor','hora_actual')
	       ->get();	
	

       foreach ($servicios as $key => $servicio){
	
	$id = $servicio->id;
	$time_now = $servicio->hora_actual;

	DB::table('airlink_u.servicio')
		->where('id',$id)
		->update(['en_destino_real' => $time_now,'fin_recogida_real' => $time_now]);

	$body = "Se ha finalizado el servicio #"+$id;
	$this->enviarNotificacion($servicio->id_conductor,$body);

       }


    } 

     private function enviarNotificacion($id_conductor, $body){
        $conductor = DB::table('airlink_u.conductor')
            ->where('id', $id_conductor)
            ->first();
        $user_token = $conductor->firebase_token;

        $title="Servicio Finalizado";
      //  $body="Se ha recargado la cuenta con $".$valor;

         $headers = '{
                          "to": "'.$user_token.'"';
          $headers .= ',"content_available": true';
          $headers.= ',"notification":{
                              "title": "'.$title.'",
                              "body": "'.$body.'",
                              "sound": "default"
                          }';
        $headers.='}';


        /*CURL PARA ENVIAR LA NOTIFICACIÓN PUSH*/
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Key='."AAAAXySWgPI:APA91bFoqqBFKwH0k4Vgwsl7c12yDzSkeQ0kVsd8MOZuNwrwAluh4vcK0cHMlwLd2R5PFrUviu_N-ZMPCz7gGQen9k3uxmExqa7SK_yX1_v7D5TZIKtenNKf_Q_67as459W5GNPc2CEL",'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        //Edit: prior variable $postFields should be $postfields;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        $result = curl_exec($ch);
        /*FINAL DE CURL PARA ENVIAR LA NOTIFICACIÓN PUSH*/


 }

}
