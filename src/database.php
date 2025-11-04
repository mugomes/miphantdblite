<?php
// Copyright (C) 2025 Murilo Gomes Julio
// SPDX-License-Identifier: MIT

// Site: https://mugomes.github.io

namespace MiPhantDBLite;

class database
{
    protected mixed $sConecta;
    protected mixed $sResult;
    protected mixed $sData;

    protected bool $sSandbox = false;

    protected bool $sPrepare = false;
    protected bool $sFechaResult = true;

    // Select
    protected array $sColumns = [];

    // Select, Insert, Update e Delete
    protected array $sTable = [];

    // Insert e Update
    protected array $sValores = [];

    // Where para Select, Update e Delete
    protected array $sWhere = [];
    private bool $sWhereColumn = false;
    private bool $sAnd = true;
    private bool $sLike = false;
    private bool $sIn = false;

    protected array $sOrders = [];
    private bool $sAsc = true;

    protected string $sLimit = '';

    public const READWRITE = SQLITE3_OPEN_READWRITE;
    public const CREATEONLY = SQLITE3_OPEN_CREATE;
    public const READONLY = SQLITE3_OPEN_READONLY;

    public function __construct(array $data, bool $sandbox = false, int $options = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE)
    {
        try {
            $this->sConecta = new \SQLite3($data['filename'], $options);

            $this->sSandbox = $sandbox;
        } catch (\SQLite3Exception $ex) {
            $this->errorLog($ex);
            exit;
        }
    }

    protected function errorLog(mixed $ex) {
        if ($this->sSandbox) {
            echo $ex->__toString();
        } else {
            error_log($ex->__toString());
        }
    }

    public function activatePrepared() {
        $this->sPrepare = true;
        return $this;
    }

    public function column(string $name) {
        $this->sColumns[] = $name;
        return $this;
    }

    public function table(string $name) {
        $this->sTable[] = $name;
        return $this;
    }

    public function or() {
        $this->sAnd = false;
        return $this;
    }

    public function like() {
        $this->sLike = true;
        return $this;
    }

    public function compareColumns() {
        $this->sWhereColumn = true;
        return $this;
    }

    public function in() {
        $this->sIn = true;
        return $this;
    }

    public function where(string $name, string|int $value, string $apelido = '') {
        $sAndOr = ($this->sAnd) ? ' AND ' : ' OR ';
        $this->sWhere[] = [
            'apelido' => $apelido,
            'nome' => $name,
            'valor' => $value,
            'andor' => $sAndOr,
            'like' => $this->sLike,
            'column' => $this->sWhereColumn,
            'in' => $this->sIn
        ];

        $this->sAnd = true;
        $this->sLike = false;
        $this->sWhereColumn = false;
        $this->sIn = false;
        return $this;
    }

    public function desc() {
        $this->sAsc = false;
        return $this;
    }

    public function order(string $name) {
        $sAscDesc = ($this->sAsc) ? ' ASC ' : ' DESC ';

        $this->sOrders[] = [
            'nome' => $name,
            'ordem' => $sAscDesc
        ];

        $this->sAsc = true;
        return $this;
    }

    public function limit(int $page = 0, int $quantidade = 1) {
        $this->sLike = $page . ',' . $quantidade;
        return $this;
    }

    public function insertValue(string $name, string $value, string $apelido = '') {
        $this->sValores[] = [
            'apelido' => $apelido,
            'nome' => $name,
            'valor' => $value
        ];

        return $this;
    }

    protected function getWhere(): string {
        $sql = '';

        if (!empty($this->sWhere)) {
            $sql .= ' WHERE ';

            $sIn = 1;
            foreach ($this->sWhere as $row) {
                if (empty($row['column'])) {
                    if ($this->sPrepare) {
                        $apelido = $row['apelido'] ?: $row['nome'];
                        if (empty($row['like'])) {
                            if ($row['in']) {
                                $sql .= $row['nome'] . '=:' . $apelido . $sIn . $row['andor'];
                                $sIn += 1;
                            } else {
                                $sql .= $row['nome'] . '=:' . $apelido . $row['andor'];
                            }
                        } else {
                            $sql .= $row['nome'] . ' LIKE :' . $apelido . $row['andor'];
                        }
                    } else {
                        if (empty($row['like'])) {
                            if (is_int($row['valor'])) {
                                $sql .= $row['nome'] . '=' . $row['valor'] . $row['andor'];
                            } else {
                                $sql .= $row['nome'] . '="' . $row['valor'] . '"';
                            }
                        } else {
                            if (is_int($row['valor'])) {
                                $sql .= $row['nome'] . ' LIKE '. $row['valor'] . $row['andor'];
                            } else {
                                $sql .= $row['nome'] . ' LIKE "%' . $row['valor'] . '%"' . $row['andor'];
                            }
                        }
                    }
                } else {
                    if (empty($row['like'])) {
                        $sql .= $row['nome'] . '=' . $row['value'] . $row['andor'];
                    } else {
                        $sql .= $row['nome'] . ' LIKE ' . $row['value'] . $row['andor'];
                    }
                }
            }

            $sql = rtrim($sql, ' AND ');
            $sql = rtrim($sql, ' OR ');
        }

        return $sql;
    }

    protected function getOrder(): string {
        $sql = '';
        if (!empty($this->sOrders)) {
            $sql .= ' ORDER BY ';
            foreach ($this->sOrders as $row) {
                $sql .= $row['nome'] . ' ' . $row['ordem'] . ',';
            }

            $sql = rtrim($sql, ',');
        }
        return $sql;
    }

    protected function getLimit(): string {
        $sql = (empty($this->sLimit)) ? '' : " LIMIT {$this->sLimit} ";
        return $sql;
    }

    public function close() {
        if ($this->sFechaResult) {
            if ($this->sPrepare) {
                $this->sData = null;
                $this->sResult->Close();
            } else {
                $this->sResult = null;
            }
        }

        $this->sConecta->close();
    }
}
