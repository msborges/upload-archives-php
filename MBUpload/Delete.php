<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Maicon Borges
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MBUpload;

class Delete {
	/**
     * Verificação das variáveis que não podem ser nulas
     */
    if (empty($realName)) {
        return ['status' => 0, 'error' => '01 - $realName vazio', 'msg' => 'O nome real do arquivo é obrigatório.'];
    }
    if (empty($pathServer)) {
        return ['status' => 0, 'error' => '02 - $sizeArchive vazio', 'msg' => 'O tamanho real do arquivo é obrigatório.'];
    }

    //Verifica se a variável $personalFolder retornou vazia, se não retornou vazia concat com a $pathServer para ter o caminho final
    if (empty($personalFolder)) {
        $finalPath = $pathServer;
    } else {
        $finalPath = $pathServer.$personalFolder;
    }
    //Verifica se o caminho final existe, caso não exista retorna array com o erro
    if (is_dir($finalPath)) {
        //Verifica se o arquivo existe no caminho final, caso não exista retorna array com erro
        if (file_exists($finalPath.$realName)) {
            //Tenta deletar o arquivo
            $deleteArchive = unlink($finalPath.$realName);
            //Caso deletado com sucesso, retorna array de sucesso, caso erro, retorna array com erro
            if ($deleteArchive) {
                return ['status' => 1, 'msg' => 'Arquivo deletado do servidor com sucesso.'];
            } else {
                return ['status' => 0, 'error' => '05 - arquivo encontrado, mas houve um erro ao deletar', 'msg' => 'Arquivo encontrado na pasta do servidor. Mas ocorreu um erro ao deletar.'];
            }
        } else {
            return ['status' => 0, 'error' => '04 - arquivo não encontrado no diretório', 'msg' => 'Arquivo não encontrado na pasta do servidor.'];
        }
    } else {
        return ['status' => 0, 'error' => '03 - $finalPath não é um diretório válido', 'msg' => 'Erro no servidor, caminho físico não encontrado.'];
    }
}