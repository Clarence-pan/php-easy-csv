<?php

namespace Clarence;

/**
 * 让导出csv更简单点.
 */
class EasyCsv
{
    /**
     * @var string 作者
     */
    public $author = 'Unknown';

    /**
     * @var string 文档标题(sheet的标题)
     */
    public $title = 'sheet1';

    /**
     * @var string 文件名
     */
    public $fileName = 'export';

    /**
     * @var array 列映射 [ excel的列名要 => 导出的数据的key | function($item,){ return[xxxx]; }  ]
     */
    public $columns;

    /**
     * 导出数据.
     *
     * @param array $dataList 要导出的数据表
     * @param array $options  选项，即是本类的字段的数组
     */
    public static function export($dataList, $options = array())
    {
        $excel = new static();

        foreach ($options as $key => $value) {
            $excel->{$key} = $value;
        }

        $excel->send($dataList);
    }

    public function send($dataList)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.rawurlencode($this->fileName).'.csv"');
        header('Cache-Control: must-revalidate,post-check=0,max-age=0');
        header('Expires:0');
        header('Pragma:public');

        $csvFile = fopen('php://output', 'w');

        if (!empty($dataList)) {
            // 构造数据
            if (!$this->columns) {
                // 如果没有定义列名，则默认取第一行数据的列名
                $this->columns = array_combine(array_keys($dataList[0]), array_keys($dataList[0]));
            }

            $excelColumnChars = range('A', chr(ord('A') + count($this->columns)));
            $titleList = array_keys($this->columns);

            fputcsv($csvFile, self::formatCsvRow($titleList));

            //$list = array();
            foreach ($dataList as $lineIndex => $lineData) {
                $exportRow = [];
                foreach ($this->columns as $excelColumn => $phpColumn) {
                    if ($phpColumn instanceof \Closure) {
                        $exportRow[] = strval($phpColumn($lineData, $lineIndex));
                    } else {
                        $exportRow[] = strval($lineData[$phpColumn]);
                    }
                }

                fputcsv($csvFile, self::formatCsvRow($exportRow));
            }
        }
    }

    public static function formatCsvRow($list)
    {
        return array_map(function ($item) {
            return iconv('utf-8', 'gbk', $item);
        }, $list);
    }
}
