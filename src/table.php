<?php
// Copyright (C) 2025 Murilo Gomes Julio
// SPDX-License-Identifier: MIT

// Site: https://mugomes.github.io

namespace MiPhantDBLite;

class table extends database
{
    public bool $ctInteger = false;
    public bool $ctNull = false;
    public bool $ctPrimaryKey = false;
    public bool $ctAutoIncrement = false;
    public string $ctDefaultValue = '';

    private array $ctColumn = [];

    private function clean()
    {
        $this->ctInteger = false;
        $this->ctNull = false;
        $this->ctAutoIncrement = false;
        $this->ctPrimaryKey = false;
        $this->ctDefaultValue = '';
    }

    private function cleanAll()
    {
        $this->clean();
        $this->ctColumn = [];
        $this->sTable = [];
    }

    public function setInt()
    {
        $this->ctInteger = true;
        return $this;
    }

    public function setNull()
    {
        $this->ctNull = true;
        return $this;
    }

    public function primaryKey()
    {
        $this->ctPrimaryKey = true;
        return $this;
    }

    public function autoIncrement()
    {
        $this->ctAutoIncrement = true;
        return $this;
    }

    public function defaultValue(string $value)
    {
        $this->ctDefaultValue = $value;
        return $this;
    }

    public function insertColumn(string $name)
    {
        $sql = $name;
        $sql .= ($this->ctInteger) ? ' INTEGER' : ' TEXT';
        $sql .= ($this->ctNull) ? ' NULL' : ' NOT NULL';
        $sql .= ($this->ctPrimaryKey) ? ' PRIMARY KEY' : '';
        $sql .= ($this->ctAutoIncrement) ? ' AUTOINCREMENT' : '';

        $this->ctColumn[] = $sql;

        $this->ctInteger = false;
        $this->ctNull = false;
        $this->ctPrimaryKey = false;
        $this->ctAutoIncrement = false;
        $this->ctDefaultValue = false;
        return $this;
    }

    public function create()
    {
        try {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->sTable[0] . ' (' . implode(',', $this->ctColumn) . ');';
            $this->sConecta->exec($sql);
            $this->cleanAll();
        } catch (\SQLite3Exception $ex) {
            $this->errorLog($ex);
        }
    }

    public function exec(string $sql)
    {
        $this->sConecta->exec($sql);
    }
}
