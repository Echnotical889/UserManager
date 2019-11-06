<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\elements\Dropdown;
use xenialdan\customui\elements\Input;
use xenialdan\customui\windows\CustomForm;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;

class API
{
    public const FRIEND_PENDING = 0;
    public const FRIEND_ACCEPTED = 1;
    public const FRIEND_DECLINED = 2;
    public const FRIEND_BLOCKED = 3;

    public const STATE_ONLINE = 1;
    public const STATE_OFFLINE = 2;
    public const STATE_UNKNOWN = 3;

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openUserUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Information");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Nick: " . $user->getDisplayName()));
        $form->addButton(new Button("Online: " . ($user->isOnline() ? "Yes" : "No")));
        $form->addButton(new Button("Manage friendship"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
            } else if ($data === "Manage friendship") {
                API::openManageUI($player, $user, $form);
            } else $player->sendForm($form);
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openRequestUserUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Information");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Nick: " . $user->getDisplayName()));
        $form->addButton(new Button("Accept"));
        $form->addButton(new Button("Reject"));
        $form->addButton(new Button("Block"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            switch ($data) {
                case "Back":
                {
                    if ($previousForm) $player->sendForm($previousForm);
                    break;
                }
                case "Accept":
                {
                    API::acceptFriendRequest($player, $user);
                    break;
                }
                case "Reject":
                {
                    API::rejectFriendRequest($player, $user);
                    break;
                }
                case "Block":
                {
                    API::openBlockUserUI($player, $user);
                    break;
                }
                default:
                    $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openManageUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Friendship");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Add friend"));//TODO remove if already friend
        $form->addButton(new Button("Remove friend"));//TODO remove if not friend
        $form->addButton(new Button("Block user"));//TODO remove if blocked
        $form->addButton(new Button("Unblock user"));//TODO remove if not blocked
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            switch ($data) {
                case "Back":
                {
                    if ($previousForm) $player->sendForm($previousForm);
                    break;
                }
                case "Add friend":
                {
                    API::openFriendConfirmUI($player, $user, $form);
                    break;
                }
                case "Remove friend":
                {
                    API::openFriendRemoveConfirmUI($player, $user, $form);
                    break;
                }
                case "Block user":
                {
                    API::openBlockUserUI($player, $user, $form);
                    break;
                }
                case "Unblock user":
                {
                    API::openUnblockUserUI($player, $user, $form);
                    break;
                }
                default:
                    $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openFriendsUI(Player $player, ?Form $previousForm = null): void
    {
        $user = Loader::$userstore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm("Friend Manager");
        $form->addButton(new Button("Add"));//TODO image
        $form->addButton(new Button("List"));//TODO image
        $form->addButton(new Button("Friend requests"));//TODO image
        $form->addButton(new Button("Search user"));//TODO image
        $form->addButton(new Button("Blocked users"));//TODO image
        #$form->addButton(new Button("Messages"));//TODO image
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
                return;
            }
            switch ($data) {
                case "Add":
                {
                    API::openFriendAddUI($player);
                    break;
                }
                case "List":
                {
                    API::openFriendListUI($player, $form);
                    break;
                }
                case "Friend requests":
                {
                    API::openFriendRequestUI($player, $form);
                    break;
                }
                case "Search user":
                {
                    API::openUserSearchUI($player);
                    break;
                }
                case "Blocked users":
                {
                    API::openBlockedListUI($player, $form);
                    break;
                }
                default:
                    $player->sendMessage("TODO: $data");
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     */
    public static function openFriendAddUI(Player $player): void
    {
        $form = new CustomForm("Friend Manager - Add");
        $form->addElement(new Input("Search user", "Username"));
        $options = array_values(array_map(function (User $user): string {
            return $user->getRealUsername();
        }, array_filter(Loader::$userstore::getUsers(), function (User $user) use ($player): bool {
            return $user->getUsername() !== $player->getLowerCaseName();
        })));
        $form->addElement(new Dropdown("Select user", $options));
        $form->setCallable(function (Player $player, array $data) use ($form): void {
            if (empty(($name = $data[0]))) $name = $data[1];
            if (($user = (Loader::$userstore::getUserByName($name))) instanceof User && $user->getUsername() !== $player->getLowerCaseName()) {
                API::openFriendConfirmUI($player, $user, $form);
            } else {
                API::openUserNotFoundUI($player, $name, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     */
    public static function openUserSearchUI(Player $player): void
    {
        $form = new CustomForm("Friend Manager - Search");
        $form->addElement(new Input("Search user", "Username"));
        $form->setCallable(function (Player $player, array $data) use ($form): void {
            if (($user = (Loader::$userstore::getUserByName($name = $data[0]))) instanceof User && $user->getUsername() !== $player->getLowerCaseName()) {
                API::openUserUI($player, $user, $form);
            } else {
                API::openUserNotFoundUI($player, $name, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openFriendConfirmUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Add", "Add " . $friend->getRealUsername() . " as friend?");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::sendFriendRequest($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openFriendRemoveConfirmUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Remove", "Remove " . $friend->getRealUsername() . " from friends?");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::removeFriend($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openBlockUserUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Block", "Block " . $friend->getRealUsername() . "? This will hide chat messages and block conversation with this user.");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::blockUser($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openUnblockUserUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Unblock", "Unblock " . $friend->getRealUsername() . "? This will show chat messages and allow conversation with this user.");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::removeFriend($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param string $name
     * @param Form|null $previousForm
     * @throws \InvalidArgumentException
     */
    public static function openUserNotFoundUI(Player $player, string $name, ?Form $previousForm = null): void
    {
        $form = new ModalForm("Friend Manager - Error", "User " . $name . " not found!", "Back", "Cancel");
        $form->setCallable(function (Player $player, bool $data) use ($previousForm): void {
            if ($data) {
                if ($previousForm) $player->sendForm($previousForm);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openFriendListUI(Player $player, ?Form $previousForm = null): void
    {
        $user = Loader::$userstore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getFriends($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - List");
            foreach ($user->getFriendsFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(($friend->isOnline() ? TextFormat::DARK_GREEN : TextFormat::DARK_RED) . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openUserUI($player, Loader::$userstore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openBlockedListUI(Player $player, ?Form $previousForm = null): void
    {
        $user = Loader::$userstore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getBlocks($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - Blocked users");
            foreach ($user->getFriendsFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(TextFormat::DARK_RED . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openUserUI($player, Loader::$userstore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openFriendRequestUI(Player $player, ?Form $previousForm = null): void
    {
        $user = Loader::$userstore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getFriendRequests($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - Requests");
            foreach ($user->getFriendsFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(($friend->isOnline() ? TextFormat::DARK_GREEN : TextFormat::DARK_RED) . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openRequestUserUI($player, Loader::$userstore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    public static function sendFriendRequest(Player $player, User $friend): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_PENDING, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Friend request sent to " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage("Got friend request by " . $user->getDisplayName());
                }
            });
        }
    }

    public static function removeFriend(Player $player, User $friend): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->removeUserRelation($user->getId(), $friend->getId(), function (int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Friend " . $friend->getDisplayName() . " removed");
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " removed you from their friends");
                }
            });
        }
    }

    public static function acceptFriendRequest(Player $player, User $friend): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_ACCEPTED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Accepted friend request by " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " accepted your friend request!");
                }
            });
        }
    }

    public static function rejectFriendRequest(Player $player, User $friend): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_DECLINED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Rejected friend request by " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " did not accept your friend request.");
                }
            });
        }
    }

    public static function blockUser(Player $player, User $friend): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_BLOCKED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Blocked user " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " blocked you.");
                }
            });
        }
    }

    public static function sendJoinMessages(Player $player): void
    {
        if (($user = Loader::$userstore::getUser($player)) instanceof User) {
            //Op messages
            if ($player->isOp()) {
                $player->sendMessage(count(array_filter(Loader::$userstore::getUsers(), function (User $user): bool {
                        return $user->isOnline();
                    })) . "/" . count(Loader::$userstore::getUsers()) . " registered users online right now");
            }
            //Friend messages
            Loader::$queries->getFriends($user->getId(), function (array $rows) use ($player, $user): void {
                $friends = $user->getFriendsFromRelationship($rows, $user->getId());
                $onlineFriends = array_filter($friends, function (User $friend): bool {
                    return $friend->isOnline();
                });
                $player->sendMessage(count($onlineFriends) . "/" . count($friends) . " Friends online:");
                $player->sendMessage(implode(", ", array_map(function (User $friend) {
                    return $friend->getDisplayName();
                }, $onlineFriends)));
            });
            Loader::$queries->getFriendRequests($user->getId(), function (array $rows) use ($player, $user): void {
                $friends = $user->getFriendsFromRelationship($rows, $user->getId());
                $player->sendMessage("You got " . count($friends) . " open friend requests");
                $player->sendMessage(implode(", ", array_map(function (User $friend) {
                    return $friend->getDisplayName();
                }, $friends)));
            });
            //Messages
        }
    }
}