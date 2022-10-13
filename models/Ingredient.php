<?php

    require_once('Base.php');

    class Ingredient extends Base {

        private $name;
        private $kj_per_100g;

        private function __construct($row = null) {
            parent::__construct($row);

            // $name = isset($row['name']) ? $row['name'] : null;
            // $kj_per_100g = isset($row['kj_per_100g']) ? $row['kj_per_100g'] : null;

            $name = parent::getValueFromRow($row, 'name');
            $kj_per_100g = parent::getValueFromRow($row, 'kj_per_100g', null, FieldTypeEnum::DOUBLE);

            $this->name = new Field('name', 'VARCHAR(50)', $name);
            $this->kj_per_100g = new Field('kj_per_100g', 'DOUBLE(10,2)', $kj_per_100g, 'UNSIGNED');
        }

        public static function syncTable()
        {
            // TODO   
        }

        public static function create()
        {
            // TODO
        }

        public static function fetch($id)
        {
            // TODO
        }

        public static function first($where)
        {
            // TODO
        }

        public static function find($where)
        {
            // TODO
        }

        public function save()
        {
            // TODO
        }

        public function destroy()
        {
            // TODO
        }

    }


?>