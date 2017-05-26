<?php
/**
 * File containing the ezcQuerySelectFirebird class.
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
 * Firebird Server specific implementation of ezcQuery.
 *
 * This class reimplements the LIMIT method in which the
 * Firebird Server differs from the standard implementation in ezcQuery.
 *
 * @see ezcQuery
 * @package Database
 * @version //autogentag//
 */
class ezcQuerySelectFirebird extends ezcQuerySelect
{
    /**
     * If a limit and/or offset has been set for this query.
     *
     * @var bool
     */
    private $hasLimit = false;

    /**
     * The limit set.
     *
     * @var int
     */
    private $limit = 0;

    /**
     * The offset set.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Resets the query object for reuse.
     *
     * @return void
     */
    public function reset()
    {
        $this->hasLimit = false;
        $this->limit = 0;
        $this->offset = 0;
        parent::reset();
    }

    /**
     * Returns SQL that limits the result set.
     *
     * $limit controls the maximum number of rows that will be returned.
     * $offset controls which row that will be the first in the result
     * set from the total amount of matching rows.
     *
     * @param int $limit integer expression
     * @param int $offset integer expression
     * @return void
     */
    public function limit( $limit, $offset = 0 )
    {
        $this->hasLimit = true;
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * Transforms the query from the parent to provide LIMIT functionality.
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        if ( $this->hasLimit )
        {
            $offset = $this->offset + $this->limit;
            if ( $this->offset > 0 ) 
            {
                $limit = $this->offset + 1;
                $query = "{$query} ROWS {$limit} TO {$offset}";
            }
            else 
            {
                $limit = $this->limit;
                $query = " {$query} ROWS {$limit}";
            }            
        }
        return $query;
    }
    
}

?>
