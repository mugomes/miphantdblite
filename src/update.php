<?php
// Copyright (C) 2025 Murilo Gomes Julio
// SPDX-License-Identifier: MIT

// Site: https://mugomes.github.io

namespace MiPhantDBLite;

class update extends database
{
    public function updateValue(string $name, string $value, string $nickname = '')
    {
        $this->sValores[] = [
            'nickname' => $nickname,
            'nome' => $name,
            'valor' => $value
        ];

        return $this;
    }

    public function update()
    {
        try {
            $sql = 'UPDATE ' . $this->sTable[0] . ' SET ';
            if ($this->sPrepare) {
                foreach ($this->sValores as $row) {
                    $nickname = $row['nickname'] ?: $row['nome'];
                    $sql .= $row['nome'] . '=:' . $nickname . ',';
                }
            } else {
                foreach ($this->sValores as $row) {
                    $sql .= "{$row['nome']}='{$row['valor']}',";
                }
            }

            $sql = rtrim($sql, ',');
            $sql .= $this->getWhere();

            if ($this->sPrepare) {
                if ($this->sResult = $this->sConecta->prepare($sql)) {
                    foreach ($this->sValores as $row) {
                        $nickname = $row['nickname'] ?: $row['nome'];
                        $this->sResult->bindParam(':' . $nickname, $row['valor']);
                    }

                    foreach ($this->sWhere as $row) {
                        $nickname = $row['nickname'] ?: $row['nome'];
                        $this->sResult->bindParam(':' . $nickname, $row['valor']);
                    }

                    $this->sResult->execute();
                    $this->sFechaResult = true;
                } else {
                    $this->sFechaResult = false;
                }
            } else {
                $this->sConecta->query($sql);
                $this->sFechaResult = false;
            }
        } catch (\SQLite3Exception $ex) {
            $this->errorLog($ex);
        } finally {
            $this->sValores = [];
            $this->sWhere = [];
            return $this;
        }
    }
}
