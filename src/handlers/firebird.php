<?php
/**
 * File containing the ezcDbHandlerFirebird class.
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
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Firebird driver implementation
 *
 * @see ezcDbHandler
 * @package Database
 * @version //autogentag//
 */
class ezcDbHandlerFirebird extends ezcDbHandler
{
    
    /**
     * Constructs a handler object from the parameters $dbParams.
     *
     * Supported database parameters are:
     * - dbname|database: Database name
     * - user|username:   Database user name
     * - pass|password:   Database user password
     * - host|hostspec:   Name of the host database is running on
     * - port:            TCP port
     * - charset:         Client character set
     *
     * @throws ezcDbMissingParameterException if the database name was not specified.
     * @param array $dbParams Database connection parameters (key=>value pairs).
     */
    public function __construct( $dbParams )
    {
        
        
        $database = null;
        $charset  = null;
        $host     = null;
        $port     = null;
        
        foreach ( $dbParams as $key => $val )
        {
            switch ( $key )
            {
                case 'database':
                case 'dbname':
                    $database = $val;
                    break;

                case 'charset':
                    $charset = $val;
                    break;

                case 'host':
                case 'hostspec':
                    $host = $val;
                    break;

                case 'port':
                    $port = $val;
                    break;
            }
        }

        if ( !isset( $database ) )
        {
            throw new ezcDbMissingParameterException( 'database', 'dbParams' );
        }

        $dsn = "firebird:dbname=";

        if ( isset( $host ) && $host )
        {
            $dsn .= "$host";
        }

        if ( isset( $port ) && $port )
        {
            $dsn .= "/$port";
        }

        if ( isset( $database ) && $database )
        {
            $dsn .= ":$database";
        }
        
        if ( isset( $charset ) && $charset )
        {
            $dsn .= ";charset=$charset";
        }
        
        parent::__construct( $dbParams, $dsn );
    } 

    /**
     * Returns 'firebird'.
     *
     * @return string
     */
    static public function getName()
    {
        return 'firebird';
    }

    
    /**
     * Begins a transaction.
     *
     * This method executes a begin transaction query unless a
     * transaction has already been started (transaction nesting level > 0 )
     *
     * Each call to beginTransaction() must have a corresponding commit() or
     * rollback() call.
     *
     * @see commit()
     * @see rollback()
     * @return bool
     */    
    public function beginTransaction()
    {
        $retval = true;
        if ( $this->transactionNestingLevel == 0 )
        {
            $this->setAttribute( PDO::ATTR_AUTOCOMMIT,false ); //disable firebird autocommit
            $retval = parent::beginTransaction();
        }
        // else NOP

        //$this->transactionNestingLevel++; Does not increase "transactionNestingLevel" because parent do this
        return $retval;
    }

    /**
     * Commits a transaction.
     *
     * If this this call to commit corresponds to the outermost call to
     * beginTransaction() and all queries within this transaction were
     * successful, a commit query is executed. If one of the queries returned
     * with an error, a rollback query is executed instead.
     *
     * This method returns true if the transaction was successful. If the
     * transaction failed and rollback was called, false is returned.
     *
     * @see beginTransaction()
     * @see rollback()
     * @return bool
     */
    public function commit()
    {
        if ( $this->transactionNestingLevel <= 0 )
        {
            $this->transactionNestingLevel = 0;

            throw new ezcDbTransactionException( "commit() called before beginTransaction()." );
        }

        $retval = true;
        if ( $this->transactionNestingLevel == 1 )
        {
            if ( $this->transactionErrorFlag )
            {
                parent::rollback();
                $this->setAttribute( PDO::ATTR_AUTOCOMMIT,true ); //enable firebird autocommit
                $this->transactionErrorFlag = false; // reset error flag
                $retval = false;
            }
            else
            {
                parent::commit();
                $this->setAttribute( PDO::ATTR_AUTOCOMMIT,true ); //enable firebird autocommit
            }
        }
        // else NOP

        //$this->transactionNestingLevel--;  Does not decrease "transactionNestingLevel" because parent do this
        return $retval;
    }    
    
    /**
     * Rollback a transaction.
     *
     * If this this call to rollback corresponds to the outermost call to
     * beginTransaction(), a rollback query is executed. If this is an inner
     * transaction (nesting level > 1) the error flag is set, leaving the
     * rollback to the outermost transaction.
     *
     * This method always returns true.
     *
     * @see beginTransaction()
     * @see commit()
     * @return bool
     */    
    public function rollback()
    {
        if ( $this->transactionNestingLevel <= 0 )
        {
            $this->transactionNestingLevel = 0;
            throw new ezcDbTransactionException( "rollback() called without previous beginTransaction()." );
        }

        if ( $this->transactionNestingLevel == 1 )
        {
            parent::rollback();
            $this->setAttribute( PDO::ATTR_AUTOCOMMIT,true );
            $this->transactionErrorFlag = false; // reset error flag
        }
        else
        {
            // set the error flag, so that if there is outermost commit
            // then ROLLBACK will be done instead of COMMIT
            $this->transactionErrorFlag = true;
        }

        //$this->transactionNestingLevel--; Does not decrease "transactionNestingLevel" because parent do this
        return true;
    }
   
    /**
     * Returns a new ezcQuerySelectFirebird derived object with Firebird Server
     * implementation specifics.
     *
     * @return ezcQuerySelectFirebird
     */
    public function createSelectQuery()
    {
        return new ezcQuerySelectFirebird( $this );
    }
    
    /**
     * Returns a new ezcQueryExpression derived object with Firebird implementation specifics.
     *
     * @return ezcQueryExpressionFirebird
     */
    public function createExpression()
    {
        return new ezcQueryExpressionFirebird( $this );
    }    
    
    
}
?>
