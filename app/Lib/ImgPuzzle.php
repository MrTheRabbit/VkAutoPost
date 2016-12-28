<?php

namespace App\Lib;

use DB;
 
/**
 * ImgPuzzle
 * Класс по поиску дубликатов изобращений.
 * 
 * @package 
 * @author TheRabbit
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class ImgPuzzle {
	
	/**
	 * ImgPuzzle::indexImg()
	 * Получение сигнатур файла.
	 * 
	 * @param mixed $strFile имя файла
	 * @return array
	 */
	public static function indexImg($strFile) {
		$arrWords = [];
		
		if (file_exists($strFile)) {
			
			$strCvec = puzzle_fill_cvec_from_file($strFile);
			
			$arrWords = [];
			$intWordlen = 10;
			$intWordCNT = 100;
			
			for ($intI = 0; $intI < min($intWordCNT, strlen($strCvec)-$intWordlen+1); $intI++) {
				$arrWords[] = bin2hex(substr($strCvec, $intI, $intWordlen));
			}//\\ for
			
		}//\\ if
		
		return $arrWords;
	}//\\ indexImg
	
	/**
	 * ImgPuzzle::addSigDB()
	 * Добавляет сигнатуры в базу.
	 * 
	 * @param mixed $arrWords сигнатуры
	 * @param mixed $intFileID ID файла в базе
	 * @return void
	 */
	public static function addSigDB($arrWords, $intFileID) {
		if ($intFileID && !DB::table('tumblr_img_sig_words')->where('file_id', $intFileID)->value('file_id')) {
			$arrData = [];
			foreach ($arrWords as $intIndex => $strWord) {
				$arrData[] = ['file_id' => $intFileID, 'sig_word' => $intIndex.'__'.$strWord];
			}//\\foreach
			if (count($arrData)) DB::table('tumblr_img_sig_words')->insert($arrData);
		}//\\ if
	}//\\ addSigDB
	
	
	/**
	 * ImgPuzzle::searchDublicat()
	 * Ищет дубликат в базе.
	 * 
	 * @param mixed $strFile имя файла
	 * @return array
	 */
	public static function searchDublicat($strFile) {
		$arrWords = self::indexImg($strFile);
		$arrData = [];
		foreach ($arrWords as $intIndex => $strWord) {
			$arrData[] = ['sig_word' => $intIndex.'__'.$strWord];
		}//\\foreach
		if (count($arrData)) {
			$arrT = DB::table('tumblr_img_sig_words')->select('file_id', DB::raw('COUNT(file_id) as cnt_file_id'))->whereIn('sig_word', $arrData)->groupBy('file_id')->get();
			if (count($arrT)) return $arrT;
		}//\\ if
		return false;
	}//\\ searchDublicat
	
	/**
	 * ImgPuzzle::isFind()
	 * Проверяет результаты и возвращает вердикт - дубликат или нет.
	 * 
	 * @param mixed $arrData массив, который вернул ImgPuzzle::searchDublicat().
	 * @return booblean
	 */
	public static function isFind($arrData) {
		if (count($arrData)) {
			foreach ($arrData as $arrI) {
				if ($arrI->cnt_file_id > 10) return true;
			}//\\ foreach
		}//\\ if
		return false;
	}//\\ isFind
	
}//\\ ImgPuzzle
