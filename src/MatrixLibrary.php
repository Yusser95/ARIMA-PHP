<?php

class MatrixLibrary
{

    public function multibly_matrices(array $a , array $b){

        $r=count($a);
        $c=count($b[0]);
        $p=count($b);
        if(count($a[0]) != $p){
            echo "Incompatible matrices";
            exit(0);
        }

        $result=array();
        for ($i=0; $i < $r; $i++){
            for($j=0; $j < $c; $j++){
                $result[$i][$j] = 0;
                for($k=0; $k < $p; $k++){
                    $result[$i][$j] += $a[$i][$k] * $b[$k][$j];
                }
            }
        }
        return $result;

    }
    //Gauss-Jordan elimination method for matrix inverse
    public function inverseMatrix(array $matrix)
    {
        //TODO $matrix validation

        $matrixCount = count($matrix);

        $identityMatrix = $this->identityMatrix($matrixCount);
        $augmentedMatrix = $this->appendIdentityMatrixToMatrix($matrix, $identityMatrix);
        $inverseMatrixWithIdentity = $this->createInverseMatrix($augmentedMatrix);
        $inverseMatrix = $this->removeIdentityMatrix($inverseMatrixWithIdentity);

        return $inverseMatrix;
    }

    private function createInverseMatrix(array $matrix)
    {
        $numberOfRows = count($matrix);

        for($i=0; $i<$numberOfRows; $i++)
        {
            $matrix = $this->oneOperation($matrix, $i, $i);

            for($j=0; $j<$numberOfRows; $j++)
            {
                if($i !== $j)
                {
                    $matrix = $this->zeroOperation($matrix, $j, $i, $i);
                }
            }
        }
        $inverseMatrixWithIdentity = $matrix;

        return $inverseMatrixWithIdentity;
    }

    private function oneOperation(array $matrix, $rowPosition, $zeroPosition)
    {
        if($matrix[$rowPosition][$zeroPosition] !== 1)
        {
            $numberOfCols = count($matrix[$rowPosition]);

            if($matrix[$rowPosition][$zeroPosition] === 0)
            {
                $divisor = 0.0000000001;
                $matrix[$rowPosition][$zeroPosition] = 0.0000000001;
            }
            else
            {
                $divisor = $matrix[$rowPosition][$zeroPosition];
            }

            for($i=0; $i<$numberOfCols; $i++)
            {
                if($divisor != 0)
                    $matrix[$rowPosition][$i] = $matrix[$rowPosition][$i] / $divisor;
            }
        }

        return $matrix;
    }

    private function zeroOperation(array $matrix, $rowPosition, $zeroPosition, $subjectRow)
    {
        $numberOfCols = count($matrix[$rowPosition]);

        if($matrix[$rowPosition][$zeroPosition] !== 0)
        {
            $numberToSubtract = $matrix[$rowPosition][$zeroPosition];

            for($i=0; $i<$numberOfCols; $i++)
            {
                $matrix[$rowPosition][$i] = $matrix[$rowPosition][$i] - $numberToSubtract * $matrix[$subjectRow][$i];
            }
        }

        return $matrix;
    }

    private function removeIdentityMatrix(array $matrix)
    {
        $inverseMatrix = array();
        $matrixCount = count($matrix);

        for($i=0; $i<$matrixCount; $i++)
        {
            $inverseMatrix[$i] = array_slice($matrix[$i], $matrixCount);
        }

        return $inverseMatrix;
    }

    private function appendIdentityMatrixToMatrix(array $matrix, array $identityMatrix)
    {
        //TODO $matrix & $identityMatrix compliance validation (same number of rows/columns, etc)

        $augmentedMatrix = array();

        for($i=0; $i<count($matrix); $i++)
        {
            $augmentedMatrix[$i] = array_merge($matrix[$i], $identityMatrix[$i]);
        }

        return $augmentedMatrix;
    }

    public function identityMatrix(int $size)
    {
        //TODO validate $size

        $identityMatrix = array();

        for($i=0; $i<$size; $i++)
        {
            for($j=0; $j<$size; $j++)
            {
                if($i == $j)
                {
                    $identityMatrix[$i][$j] = 1;
                }
                else
                {
                    $identityMatrix[$i][$j] = 0;
                }
            }
        }

        return $identityMatrix;
    }
}
