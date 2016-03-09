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

class Upload {
    public function transformBase64Image($base64Img = '') {
        $tempDirImage = '/tmp/';
        if (is_dir($tempDirImage)) {
            $pos  = strpos($base64Img, ';');
            $type = explode(':', substr($base64Img, 0, $pos))[1];
            $arType = explode('/', $type);
            $base64Img = str_replace('data:image/'.$arType[1].';base64,', '', $base64Img);
            $base64Img = str_replace(' ', '+', $base64Img);
            $data = base64_decode(trim($base64Img));
            $fileName = md5(trim($base64Img)).'.'.$arType[1];
            $file =  $tempDirImage.$fileName;
            $success = file_put_contents($file, $data);

            if ($success) {
                return ['status' => 1, 'error' => null, 'msg' => 'Success in converting base64 and send to the temporary folder.', 'data' => ['name' => $fileName, 'size' => sizeof($fileName), 'pathTemp' => $file]];
            } else {
                return ['status' => 0, 'error' => '02 - The image was sent not to the temporary folder.', 'msg' => 'Error sending the image to the temporary folder on the server.', 'data' => null];
            }
        } else {
            return ['status' => 0, 'error' => '01 - '.$tempDirImage.' it is not a valid directory', 'msg' => 'Server error, physical path not found.', 'data' => null];
        }
    }

    public function uploadInServer($realName = '' , $sizeArchive = 0, $tempPath = '', $newPath = '', $personalFolder = '', $arrayExtensions = [], $limitSizeUpload = 10485760, $createMD5 = true, $resizeImage = false, $maxWidthResize = 10, $zipArchive = false, $unzipFinalArchive = false) {
        /**
         * Verificação das variáveis que não podem ser nulas
         */
        if (empty($realName)) {
            return ['status' => 0, 'error' => '01 - $realName vazio', 'msg' => 'O nome real do arquivo é obrigatório.', 'data' => null];
        }
        if (empty($sizeArchive)) {
            return ['status' => 0, 'error' => '02 - $sizeArchive vazio', 'msg' => 'O tamanho real do arquivo é obrigatório.', 'data' => null];
        }
        if (empty($tempPath)) {
            return ['status' => 0, 'error' => '03 - $tempPath vazio', 'msg' => 'O caminho da tmp do PHP é obrigatório.', 'data' => null];
        }
        if (empty($newPath)) {
            return ['status' => 0, 'error' => '04 - $newPath vazio', 'msg' => 'O caminho destino do arquivo é obrigatório.', 'data' => null];
        }
        if (empty($arrayExtensions)) {
            $arrayExtensions = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'jpg', 'png', 'gif', 'zip', 'DOC', 'DOCX', 'PDF', 'XLS', 'XLSX', 'JPG', 'PNG', 'GIF', 'ZIP'];
        }

        //Captura extensão do arquivo
        $extension = pathinfo($realName, PATHINFO_EXTENSION);
        //Verifica se a extensão do arquivo está no array de extensões permitidas, caso erro retorna mensagem
        if (in_array($extension, $arrayExtensions)) {
            //Verifica se o tamanho do arquivo é <= ao limite passado por parâmetro, caso erro retorna mensagem
            if ($sizeArchive <= $limitSizeUpload) {
                //Verifica se o destino é um caminho válido, caso erro retorna mensagem
                if (is_dir($newPath)) {
                    //Se o caminho auxiliar for vazio, a variável de caminho final recebe o destino, senão
                    //Verifica se o destino + o caminho auxiliar existe, se não existir, roda o mkdir() do caminho, caso erro ao criar pasta retorna mensagem
                    //E salva o destino + caminho auxiliar na variável de caminho final
                    if (empty($personalFolder)) {
                        $finalPath = $newPath;
                        $personalFolderPath = null;
                    } else {
                        if (!is_dir($newPath.$personalFolder)) {
                            $mkdir = mkdir($newPath.$personalFolder, 0755, true);
                            if (!$mkdir) {
                                return ['status' => 0, 'error' => '08 - $personalFolder falha ao rodar o mkdir()', 'msg' => 'Erro ao criar a pasta customizada no destino final.', 'data' => null];
                            }
                        }
                        $finalPath = $newPath.$personalFolder;
                        $personalFolderPath = $personalFolder;
                    }
                    //Se md5 for TRUE, cria o nome md5, senão permanesse com o nome do arquivo
                    if ($createMD5) {
                        $newName = md5(uniqid(time())).'.'.$extension;
                    } else {
                        $newName = $realName;
                    }
                    //Move da temp do PHP para a tmp do servidor, caso erro retorna mensagem
                    if (copy($tempPath, '/tmp/'.$newName)) {
                        //Verifica se o arquivo existe na tmp do servidor, caso erro retorna mensagem
                        if (file_exists('/tmp/'.$newName)) {
                            //Instância do ZipArchive
                            $zipFunc = new \ZipArchive();
                            //Variável criada para armazenar o nome do arquivo caso entre na opção de compactar o mesmo
                            $oldNewName = '';
//                            //Verifica se a extensão do arquivo é uma imagem, para passar pelo processo de tratamento da mesma
                            if (in_array($extension, ['jpg','jpeg','png','gif','bmp','JPG','JPEG','PNG','GIF','BMP'])) {
                                //Se resize for TRUE trata a imagem recebendo a variável maxWidthResize como parâmetro de largura, a altura será proporcional a largura que foi passada por parâmetro, senão
                                //Ela trata a imagem passando por padrão a largura fullHD de 1920, imagens com largura inferior a essa só sofrerão o tratamento de peso em MB/Kb e etc
                                if ($resizeImage) {
                                    $treat = \simpleTreat::treat('/tmp/'.$newName, '/tmp/'.$newName, $maxWidthResize);
                                } else {
                                    $treat = \simpleTreat::treat('/tmp/'.$newName, '/tmp/'.$newName, 1920);
                                }
                                //Recalcula o tamanho do arquivo depois de tratado e salva na variável
                                if($treat) {
                                    $sizeArchive = filesize('/tmp/'.$newName);
                                }
                            }
                            //Se zipArchive for TRUE, é criado o arquivo zip com o nome do arquivo, caso erro retorna mensagem
                            if ($zipArchive) {
                                $newNameZip = $newName.'.zip';
                                //Abre o arquivo zip e adiciona o arquivo a compactar dentro
                                if ($zipFunc->open('/tmp/'.$newNameZip, \ZipArchive::CREATE) === true) {
                                    $zipFunc->addFile('/tmp/'.$newName, $newName);
                                } else {
                                    return ['status' => 0, 'error' => '10 - arquivo zip não pode ser criado', 'msg' => 'Erro ao compactar o arquivo em um novo arquivo zipado.', 'data' => null];
                                }
                                //Fecha arquivo zip
                                $zipFunc->close();
                                //Se depois de terminar o processo o arquivo zip existe na tmp, apaga o arquivo descompactado, atualiza o nome do arquivo e recalcula o tamanho do arquivo conforme o arquivo compactado
                                if (file_exists('/tmp/'.$newNameZip)) {
                                    unlink('/tmp/'.$newName);
                                    $oldNewName = $newName;
                                    $newName = $newNameZip;
                                    $sizeArchive = filesize('/tmp/'.$newNameZip);
                                }

                            }
                            //Copia arquivo da tmp do servidor para o destino
                            if (copy('/tmp/'.$newName, $finalPath.$newName)) {
                                //Caso teve sucesso, apaga o arquivo da pasta tmp do servidor
                                unlink('/tmp/'.$newName);
                                //Se unzipFinalArchive for TRUE, é aberto o arquivo na pasta destino para ser descompactado, caso erro retorna mensagem
                                if ($unzipFinalArchive) {
                                    //Abre o arquivo e extrai na pasta os dados usando o nome salvo no ínicio do processo
                                    if ($zipFunc->open($finalPath.$newName) === true) {
                                        $zipFunc->extractTo($finalPath, $oldNewName);
                                    } else {
                                        return ['status' => 0, 'error' => '12 - arquivo zip não pode ser descompactado', 'msg' => 'Erro ao descompactar o arquivo no destino final.', 'data' => null];
                                    }
                                    //Fecha arquivo
                                    $zipFunc->close();
                                    //Se depois de terminar o processo o arquivo descompactado existe no destino, apaga o arquivo compactado, atualiza o nome do arquivo e recalcula o tamanho do arquivo conforme o arquivo descompactado
                                    if (file_exists($finalPath.$oldNewName)) {
                                        unlink($finalPath.$newName);
                                        $newName = $oldNewName;
                                        $sizeArchive = filesize($finalPath.$newName);
                                    }

                                }
                                //Monta o array de retorno e retorna para a classe que instânciou a função
                                $data = ['realName' => $realName, 'size' => $sizeArchive, 'newName' => $newName, 'extension' => $extension, 'folderArchive' => $personalFolderPath];
                                return ['status' => '1', 'error' => null, 'msg' => 'Sucesso em salvar o arquivo no destino.', 'data' => $data];

                            } else {
                                return ['status' => 0, 'error' => '11 - não moveu da temp do servidor para o destino final', 'msg' => 'Erro ao copiar da pasta temporária do servidor para o destino final.', 'data' => null];
                            }
                        } else {
                            return ['status' => 0, 'error' => '09 - arquivo não encontrado na temp', 'msg' => 'Arquivo não encontrado na pasta temporária do servidor.', 'data' => null];
                        }
                    } else {
                        return ['status' => 0, 'error' => '08 - não moveu o arquivo da temp do php para a temp do servidor', 'msg' => 'Erro ao mover o arquivo para a pasta temporária do servidor.', 'data' => null];
                    }
                } else {
                    return ['status' => 0, 'error' => '07 - $newPath não é um diretório válido', 'msg' => 'Erro no servidor, caminho físico não encontrado.', 'data' => null];
                }
            } else {
                return ['status' => 0, 'error' => '06 - $sizeArchive é maior que $limitSizeUpload', 'msg' => 'O arquivo é maior do que o tamanho máximo permitido.', 'data' => null];
            }
        } else {
            return ['status' => 0, 'error' => '05 - $extension não está no array $arrayExtensions', 'msg' => 'A extensão do arquivo não está permitida.', 'data' => null];
        }
    }
}