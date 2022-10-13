<?php

    require_once('Junction.php');


    class RecipeIngredient extends Junction {
        
        private $recipeID;
        private $ingredientID;

        private function __construct($row = null) {

            $recipeID = parent::getValueFromRow($row, 'recipeID', null, FieldTypeEnum::INT);
            $ingredientID = parent::getValueFromRow($row, 'ingredientID', null, FieldTypeEnum::INT);

            $this->recipeID = new Field('recipeID', 'INT', $recipeID, 'NOT NULL');
            $this->ingredientID = new Field('ingredientID', 'INT', $ingredientID, 'NOT NULL');

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