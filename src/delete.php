<?php
// Copyright (C) 2025 Murilo Gomes Julio
// SPDX-License-Identifier: MIT

// Site: https://mugomes.github.io

namespace MiPhantDBLite;

class delete extends database
{
    public function delete()
    {
        try {
            $sql = 'DELETE FROM ' . $this->sTable[0];
            $sql .= $this->getWhere();

            if ($this->sPrepare) {
                if ($this->sResult = $this->sConecta->prepare($sql)) {
                    foreach ($this->sWhere as $row) {
                        $this->sResult->bindParam(':' . $row['nome'], $row['valor']);
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
            $this->sWhere = [];
            return $this;
        }
    }
}
