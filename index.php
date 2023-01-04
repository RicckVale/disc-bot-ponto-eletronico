<?php
include_once 'vendor/autoload.php';
include_once 'src/config.php';
include_once 'src/database.php';

ini_set('memory_limit', '-1');


use Discord\Discord;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

$token = TOKEN_DISC;

$discord = new Discord([
    'token' => "$token",
]);
$discord->on('ready', function (Discord $discord) {
    # Canal Principal
    $channel = $discord->getChannel(CANAL_PONTO);

    if ($channel->limitDelete(10)) {
        #Monta a Mensagem do Ponto
        $builder = MessageBuilder::new();
        $embed = [
            "title" => "Ponto Eletrônico - LSPD",
            "description" => "*Bata seu ponto para patrulhar.*\n\n 1. Será criada uma nova sala. \n2. Ao finalizar a patrulha, finalize seu ponto na sala criada.\n\n\n```\nÉ obrigatório o uso do ponto eletrônico no Discord! \n```",
            "footer" => ["text" => "Desenvolvido por @ricckvale"],
            "color" => 0x00FFFF
        ];
        $ar = ActionRow::new();
        $submit = Button::new(Button::STYLE_SUCCESS, 'abrirPonto')->setLabel('⏰ INICIAR PATRULHA');
        $ar->addComponent($submit);

        $builder->addEmbed($embed);
        $builder->addComponent($ar);
        # Envia a Mensagem
        $channel->sendMessage($builder);
        # Finaliza Mensagem do Ponto.
        $submit->setListener(function (Interaction $interaction) use ($discord) {
            if ($interaction->data->custom_id == "abrirPonto") {
                $now = (new DateTime())->format('Y-m-d H:i:s');

                $distintivo = substr($interaction->member->nick, 0, 3);
                $usuario = $interaction->member->user->id;

                # Verifica se o usuário já tem um ponto aberto
                $db = new Database();
                $db->select("ponto", "*", "distintivo = '$distintivo' AND status = 'ABERTO'");
                $result = $db->sql;
                $total = mysqli_num_rows($result);

                if ($total == 0) {
                    # Cria o ponto no banco de dados.
                    $insert = new Database();
                    $insert->insert("ponto", ['usuario' => $usuario, 'distintivo' => $distintivo, 'status' => 'ABERTO', 'entrada' => $now, 'saida' => '0000-00-00']);

                    # Pega o último ID para colocar o número na sala.
                    $ultimoId = new Database();
                    $ultimoId->lastID("ponto");
                    $result = $ultimoId->sql;
                    $pontoID = mysqli_fetch_assoc($result);
                    $pontoID = $pontoID['id'];

                    if ($insert == true) {

                        # Cria a sala do ponto no Discord.
                        $user = $interaction->member;
                        $categoria = CATEGORIA_PONTO;
                        $newchannel = $interaction->guild->channels->create([
                            'name' => '🟢・ponto-' . $pontoID,
                            'type' => '0',
                            'topic' => 'Ponto aberto!',
                            'parent_id' => "$categoria",
                            'nsfw' => false
                        ]);
                        $interaction->guild->channels->save($newchannel)->then(function (Channel $channel) use ($user, $interaction, $discord) {
                            $nowBR = (new DateTime())->format('d/m/Y H:i:s');
                            $distintivo = substr($interaction->member->nick, 0, 3);

                            $channel->setPermissions($user, ['view_channel', 'add_reactions', 'read_message_history']);
                            $pontoMsg = new Embed($discord);
                            $pontoMsg->setColor('#0x00FFFF');
                            $pontoMsg->setDescription("**Bem vindo ao trabalho $interaction->member!** \n\nVocê iniciou sua patrulha às:\n\n **$nowBR** \nDistintivo: **$distintivo**\n\n ```Não se esqueça de fechar seu ponto no final da patrulha! ``` \n\n **Divirta-se!**");

                            # Cria mensagem e botão para finalizar ponto.
                            $builder = MessageBuilder::new();
                            $builder->addEmbed($pontoMsg);
                            $actionRow = ActionRow::new();
                            $closeticket = Button::new(Button::STYLE_DANGER, "fecharPonto$channel->id")->setLabel('⏰ FINALIZAR PATRULHA');
                            $actionRow->addComponent($closeticket);
                            $builder->addComponent($actionRow);
                            $channel->sendMessage($builder);

                            $closeticket->setListener(function (Interaction $interaction) use ($discord, $channel) {
                                if ($interaction->data->custom_id === "fecharPonto$channel->id") {
                                    $channel = $discord->getChannel($interaction->channel_id);

                                    # Atualiza o Horário e Fecha o Ponto
                                    $distintivo = substr($interaction->member->nick, 0, 3);
                                    $select = new Database();
                                    $select->select("ponto", "*", "distintivo = '$distintivo' AND status = 'ABERTO'");
                                    $result = $select->sql;
                                    $select = mysqli_fetch_assoc($result);
                                    $entrada = $select['entrada'];

                                    $id = explode('-', $channel->name);
                                    $id = $id[1];
                                    $now = (new DateTime())->format('Y-m-d H:i:s');
                                    $update = new Database();
                                    $update->update("ponto", ['status' => 'FECHADO', 'saida' => $now], "id=$id");

                                    $builder = MessageBuilder::new();
                                    $embed = [
                                        "title" => "Ponto finalizado!  #$id",
                                        "description" => "\nEntrada:**$entrada** \nSaída: **$now**\nDistintivo: $distintivo\nUsuário: $interaction->member",
                                        "footer" => ["text" => "Até a próxima ❤"],
                                        "color" => 0xff0000
                                    ];
                                    $builder->addEmbed($embed);
                                    # Envia a Mensagem                                   

                                    $interaction->member->user->sendMessage($builder);

                                    # Insere o Ponto completo no Canal de Log.

                                    # Apaga o Canal
                                    $interaction->guild->channels->delete($channel);


                                    # Salva o Log
                                    $channel = $discord->getChannel(LOG_PONTO);
                                    $channel->sendMessage($builder);
                                    $i = true;
                                };
                            }, $discord);
                        });
                    }
                } else {
                    $interaction->member->user->sendMessage('**Olá!** Seu ponto já ta aberto utilize a sala já aberta!');
                }
            };
        }, $discord);
    }
});
$discord->run();
