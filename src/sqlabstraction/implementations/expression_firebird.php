<?php
/**
 * File containing the ezcQueryExpressionFirebird class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Database
 * @version 1.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * The ezcQueryExpressionFirebird class is used to create SQL expression for Firebird Server.
 *
 * This class reimplements the methods that have a different syntax in Firebird Server.
 *
 * @package Database
 * @version //autogentag//
 */
class ezcQueryExpressionFirebird extends ezcQueryExpression
{
    /**
     * Contains an interval map from generic intervals to MS SQL native intervals.
     *
     * @var array(string=>string)
     */
    protected $intervalMap = array(
        'SECOND' => 'second',
        'MINUTE' => 'minute',
        'HOUR' => 'Hour',
        'DAY' => 'Day',
        'MONTH' => 'Month',
        'YEAR' => 'Year',
    );

    /**
     * Returns the length of a text field.
     *
     * @param string $column
     * @return string
     */
    public function length( $column )
    {
        $column = $this->getIdentifier( $column );
        return "CHAR_LENGTH({$column})";
    }

    /**
     * Returns the current system date and time in the database internal
     * format.
     *
     * @return string
     */
    public function now()
    {
        return "CAST('NOW' as timestamp)";  // format for date output i.e. yyyy-mm-dd hh:mi:ss(24h)
    }

    /**
     * Returns a series of strings concatinated
     *
     * concat() accepts an arbitrary number of parameters. Each parameter
     * must contain an expression or an array with expressions.
     *
     * @param string|array(string) $... strings that will be concatinated.
     */
    public function concat()
    {
        $args = func_get_args();
        $cols = ezcQuery::arrayFlatten( $args );
        if ( count( $cols ) < 1 )
        {
            throw new ezcQueryVariableParameterException( 'concat', count( $args ), 1 );
        }

        $cols = $this->getIdentifiers( $cols );
        return join( ' || ' , $cols );
    }    

    /**
     * Returns the SQL to locate the position of the first occurrence of a substring
     *
     * @param string $substr
     * @param string $value
     * @param numeric $startposition
     * @return string
     */
    public function position( $substr, $value, $startposition=0 )
    {
        $value = $this->getIdentifier( $value );
        if ( $startposition > 0)
        {
            return "position( '{$substr}' in {$value}, ${startposition} )";
        }
        else 
        {
           return "position( '{$substr}' in {$value} )";
        }
    }

    /**
     * Returns the SQL to calculate the next highest integer value from the number.
     *
     * @param string $number
     * @return string
     */
    public function ceil( $number )
    {
        $number = $this->getIdentifier( $number );
        return "CEILING( {$number} )";
    }

    /**
     * Returns the SQL that converts a timestamp value to number of seconds since 1970-01-01 00:00:00-00.
     *
     * @param string $column
     * @return string
     */
    public function unixTimestamp( $column )
    {
        $column = $this->getIdentifier( $column );
        return "DATEDIFF(second, timestamp '1970-01-01 00:00:00', {$column})";
    }

    /**
     * Returns the SQL that subtracts an interval from a timestamp value.
     *
     * @param string $column
     * @param numeric $expr
     * @param string $type one of SECOND, MINUTE, HOUR, DAY, MONTH, or YEAR
     * @return string
     */
    public function dateSub( $column, $expr, $type )
    {
        $type = $this->intervalMap[$type];

        $column = $this->getIdentifier( $column );
        return "DATEADD ( {$type}, -{$expr}, {$column} )";
    }

    /**
     * Returns the SQL that adds an interval to a timestamp value.
     *
     * @param string $column
     * @param numeric $expr
     * @param string $type one of SECOND, MINUTE, HOUR, DAY, MONTH, or YEAR
     * @return string
     */
    public function dateAdd( $column, $expr, $type )
    {
        $type = $this->intervalMap[$type];

        $column = $this->getIdentifier( $column );
        return "DATEADD ( {$type}, +{$expr}, {$column} )";
    }

    /**
     * Returns the SQL that extracts parts from a timestamp value from a column.
     *
     * @param string $column
     * @param string $type one of SECOND, MINUTE, HOUR, DAY, MONTH, or YEAR
     * @return string
     */
    public function dateExtract( $column, $type )
    {
        $type = $this->intervalMap[$type];

        $column = $this->getIdentifier( $column );
        return "LPAD( EXTRACT( {$type} FROM {$column} ), 2,' ' )";
    }
}
?>
