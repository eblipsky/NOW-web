<?php


class TestController extends BaseController {
    
        public function log($logid) {       
            //$url = "http://localhost:9998/pipeline/777074f7375c3b9fecff988e980032af/ionNA19788_YW4FP012_20140212_103307.log";        
            
            $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);                        
            
            $log = $client->asArray()->getDoc($logid);
            $attachment = key($log['_attachments']);           
                        
            $url = Settings::COUCHDB_HOST."/".Settings::COUCHDB_DB."/".$logid."/".$attachment;            
            $this->data['info'] = $attachment;
            
            $str = nl2br(file_get_contents($url));
            $str = str_replace("<br />", "<hr>", $str);                        
            $this->data['file'] = $str;                        
            
            return View::make('hpc/log',$this->data);        
        }
    
    
	public function index()
	{   
            $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
           
            //$dbs = $client->listDatabases();
            //print_r($dbs);
            //$info = $client->getDatabaseInfos();
            //print_r($info);
            //$id = $client->getUuids();
            //print_r($id);
            
//            $all_docs = $client->getAllDocs();
//            echo "Database got ".$all_docs->total_rows." documents.<BR>\n";
//            foreach ( $all_docs->rows as $row ) {
//                //echo "Document ".$row->id."<BR>\n";
//            }
            
            //get all pipelines
//            $pipelines = $client->getView('pipelines','all');                
//            print_r($pipelines);
            
//            foreach( $pipelines->rows as $pipeline ) {
//                //echo $pipeline->key.'['.$pipeline->id.']</br>';
//               
//                $queues = $client->key($pipeline->id)->getView('pipelines','queues');
//                foreach( $queues->rows as $queue ) {
//                    //cho '-queue:'.$queue->value->name.'</br>';
//                    
//                    $q = couchDocument::getInstance($client, $queue->id);
//                   
//                    if ( ! isset($q->active)) $q->active = False;
//                    if ( ! isset($q->nextq)) $q->nextq = '';   
//                    if ( ! isset($q->errq)) $q->errq = '';
//                    if ( ! isset($q->validchk)) $q->validchk = 'check_default';
//                    if ( ! isset($q->desc)) $q->desc = '';
//                    if ( ! isset($q->cmdtype)) $q->cmdtype = 'local';
//                    if ( ! isset($q->cmd)) $q->cmd = '';
//                    if ( ! isset($q->filecnt)) $q->filecnt = 'MAX_CPU';
//                    
//                }
//                
//            
//            }
            
        
            //HPCPipeline::create('pipeline1');
            //HPCPipeline::delete('pipeline1');
        
            //$pipeline = "test";
            
            //$p = new HPCPipeline($pipeline);
            //print_r($p->queues());
            
            $this->data['current_time'] = Carbon\Carbon::now();
            $this->data['pipelines'] = HPCPipelineCDB::all();
            return View::make('hpc/monitor',$this->data);
            
            //$q = new HPCQueue($pipeline,'start');
            //print_r($q);
            
            
//            $doc = new couchDocument($client);
//            $doc->type = 'pipeline';
//            $doc->name = 'pipeline1';
//            $id = $doc->id();
//            
//            $doc = new couchDocument($client);
//            $doc->type = 'queue';
//            $doc->name = 'start';
//            $doc->pipeline = $id;
//            
//            $doc = new couchDocument($client);
//            $doc->type = 'queue';
//            $doc->name = 'error';
//            $doc->pipeline = $id;
//            
//            $doc = new couchDocument($client);
//            $doc->type = 'queue';
//            $doc->name = 'done';
//            $doc->pipeline = $id;
//            
//            $doc = new couchDocument($client);
//            $doc->type = 'queue';
//            $doc->name = 'None';
//            $doc->pipeline = $id;
//            
//            $doc = new couchDocument($client);
//            $doc->type = 'batch';
//            $doc->name = 'batch1';
            
	}


}
