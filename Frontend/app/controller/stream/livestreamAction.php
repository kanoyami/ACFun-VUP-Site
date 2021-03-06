<?php

namespace app\controller;
use App;
use biny\lib\Language;
use Constant;

class livestreamAction extends baseAction
{
    /**
     * 每日
     */
    public function action_index()
    {
        if(!Language::getLanguage()){
            Language::setLanguage('cn', Constant::month);
        }
        $lang = $this->get('lang');
        $lang && Language::setLanguage($lang, Constant::month);

        $todayTimestamp = strtotime(date('Y-m-d'));
        $upListDataset = $this->upDetailDAO->filter([
            '<'=>array('add_date'=> $todayTimestamp)
        ])->query();

        $upListDatasets = [];
        $upListDatasetColumn = [];
        $i = 0;

        foreach ($upListDataset as $k => $upData){
            $rawLatestData = $this->upRawLiveDataDAO->filter([
                'uperid'=>$upData['uperid'],
                '>='=>array('up_date'=> $todayTimestamp)
            ])->order(array('onlineCount'=>'DESC'))->limit(1)->find();
            if($rawLatestData){
                $upListDatasetColumn[$i] = $rawLatestData['onlineCount']; //排序标准
                $upData['onlineCount'] = $rawLatestData['onlineCount'];
                $upListDatasets[$i] = $upData;
                $i ++;
            }
        }
        array_multisort($upListDatasetColumn, SORT_DESC, $upListDatasets);
        $adminData = [];
        if(App::$model->Admin->exist()){
            $adminData = App::$model->Admin->values();
        }
        return $this->display('stream/livestream', array(
            'upListData' => $upListDatasets,
            'adminData' => $adminData,
            'dayTimestamp' => $todayTimestamp
        ));
    }

    public function action_prev($day){
        if(!$day || $day <= 0){
            $this->response->redirect('/live/prev/1');
        }

        if(!Language::getLanguage()){
            Language::setLanguage('cn', Constant::month);
        }
        $lang = $this->get('lang');
        $lang && Language::setLanguage($lang, Constant::month);
        $todayTimestamp = strtotime(date('Y-m-d')) - ($day - 1) * 86400;
        $cronLatestData = $this->upLiveDataCronDAO->filter([
            '='=>array('add_date'=> $todayTimestamp - 1)
        ])->order(array('add_date'=>'DESC'))->query();

        $upListDatasets = [];
        $upListDatasetColumn = [];
        $i = 0;

        foreach ($cronLatestData as $k => $upData){
            $rawLatestData = $this->upDetailDAO->filter([
                'uperid'=>$upData['uperid'],
            ])->find();
            if($rawLatestData){
                $upListDatasetColumn[$i] = $upData['onlineCount']; //排序标准
                $upData['nowName'] = $rawLatestData['nowName'];
                $upListDatasets[$i] = $upData;
                $i ++;
            }
        }
        array_multisort($upListDatasetColumn, SORT_DESC, $upListDatasets);
        $adminData = [];
        if(App::$model->Admin->exist()){
            $adminData = App::$model->Admin->values();
        }
        return $this->display('stream/livestream', array(
            'upListData' => $upListDatasets,
            'adminData' => $adminData,
            'dayTimestamp' => $todayTimestamp - 1
        ));
    }
}