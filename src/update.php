<?php
// Copyright (C) 2025 Murilo Gomes Julio
// SPDX-License-Identifier: MIT

// Site: https://mugomes.github.io

namespace MiPhantDBLite;

class update extends database
{
    public function update()
    {
        try {
            $sql = 'UPDATE ' . $this->sTable[0] . ' SET ';
            if ($this->sPrepare) {
                foreach ($this->sValores as $row) {
                    $apelido = $row['apelido'] ?: $row['nome'];
                    $sql .= $row['nome'] . '=:' . $apelido . ',';
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
                        $apelido = $row['apelido'] ?: $row['nome'];
                        $this->sResult->bindParam(':' . $apelido, $row['valor']);
                    }

                    foreach ($this->sWhere as $row) {
                        $apelido = $row['apelido'] ?: $row['nome'];
                        $this->sResult->bindParam(':' . $apelido, $row['valor']);
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
        }
    }
}
