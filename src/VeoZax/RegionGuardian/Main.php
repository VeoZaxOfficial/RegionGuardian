<?php
namespace VeoZax\RegionGuardian;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use VeoZax\RegionGuardian\signature\VeoZax;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
    private $regions = [];
    private $economy;
    private $plans = [];
    private $playerRegions = [];
    private $blockedBlocks = [8, 9, 10, 11, 46, 51];
    private $blockedItems = [259, 325 => [8,10]];
    private $blockedTools = [272, 273, 274, 275];
    private $pendingOwnerTransfers = [];
    private $allowedWorld = "SurvivalRealm"; // Replace This World Name With Your Actual World Name.
    private $bypassUser = "veozax"; // Who Ever Uses This Plugin, Replace My Name With Your Actual Name.

    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->loadRegions();
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
           VeoZax::printBanner($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    $this->plans = [
           "dirt" => ["radius" => 10, "members" => 4, "price" => 1000],
            "coal" => ["radius" => 30, "members" => 6, "price" => 15000],
             "copper" => ["radius" => 50, "members" => 8, "price" => 50000],
              "gold" => ["radius" => 70, "members" => 10, "price" => 70000],
               "emerald" => ["radius" => 90, "members" => 12, "price" => 90000],
                "diamond" => ["radius" => 110, "members" => 14, "price" => 110000],
                 "obsidian" => ["radius" => 130, "members" => 16, "price" => 150000],
                  "netherite" => ["radius" => 150, "members" => 18, "price" => 350000],
                   "bedrock" => ["radius" => 170, "members" => 20, "price" => 450000],
                    "glowstone" => ["radius" => 190, "members" => 22, "price" => 500000],
                     "redstone" => ["radius" => 210, "members" => 24, "price" => 800000],
                      "premium" => ["radius" => 230, "members" => 26, "price" => 5000000] 
                      ];
}

       private function loadRegions(){
             $file = $this->getDataFolder() . "regions.json";
            if (file_exists($file)){
            $this->regions = json_decode(file_get_contents($file), true);
}
}
       private function saveRegions(){
            file_put_contents($this->getDataFolder() . "regions.json", json_encode($this->regions));
}
       private function getOwnedRegion($player){
            foreach ($this->regions as $name => $data){
            if ($data["owner"] === $player) return $name;
}
        return null;
}
    private function getMemberRegion($player){
        foreach ($this->regions as $name => $data){
            if (in_array($player, $data["members"])) return $name;
}
        return null;
}
    public function onCommand(CommandSender $sender, Command $cmd, $label,array $args){
    if (!$sender instanceof Player) return true;
    $name = strtolower($sender->getName());
    $allowedWorld = "SurvivalRealm";
    if ($name !== "veozax"){ // Who Ever Uses This Plugin, Replace My Name With Your Actual Name.
        if ($sender->getLevel()->getFolderName() !== $allowedWorld){
            $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cRegion commands can only be used in §eSurvivalRealm§c.");
            return true;
}
}
    if(!isset($args[0])){
        $sender->sendMessage("§8§l[ §6Region§aGuardian §fCommand List §8§l] ");
        $sender->sendMessage("§8» §b/rg create <name> §7- Create a new region");
        $sender->sendMessage("§8» §b/rg plans §7- Show available plans");
        $sender->sendMessage("§8» §b/rg buy <plan> §7- Upgrade your region plan");
        $sender->sendMessage("§8» §b/rg addmember <player> §7- Add a member");
        $sender->sendMessage("§8» §b/rg kick <player> §7- Remove a member");
        $sender->sendMessage("§8» §b/rg setowner <player> §7- Transfer ownership");
        $sender->sendMessage("§8» §b/rg accept §7- Accept ownership transfer");
        $sender->sendMessage("§8» §b/rg deny §7- Deny ownership transfer");
        $sender->sendMessage("§8» §b/rg delete <region> §7- Delete your region");
        $sender->sendMessage("§8» §b/rg leave §7- Leave a region you are a member of");
        $sender->sendMessage("§8» §b/rg list [page] §7- List regions");
        $sender->sendMessage("§8» §b/rg myinfo §7- View your region info");
        $sender->sendMessage("§8» §b/rg seeinfo <region> §7- View region info");
        $sender->sendMessage("§8» §c/rg remove <region> §7- Force remove any region (Only Owner Can Do It) ");
        $sender->sendMessage("§8» §6Region§aGuard §ePlugin §aDeveloped §bBy §fVeoZax.");
        return true;
}
    $sub = strtolower($args[0]);
    switch ($sub){
        case "create":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg create <name>");
        break;
}
    $rname = strtolower($args[1]);
    if (isset($this->regions[$rname])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cA region with that name already exists.");
        break;
}
    $level = $sender->getLevel()->getFolderName();
    $radius = $this->plans["dirt"]["radius"];
    $x = (int)$sender->getX();
    $z = (int)$sender->getZ();
    $x1 = $x - $radius;
    $z1 = $z - $radius;
    $x2 = $x + $radius;
    $z2 = $z + $radius;
    if ($this->overlaps($x1, $z1, $x2, $z2, $level)){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou Cannot create a region here, There's already someone claimed region. please find an unclaimed region and set your region there.");
        break;
}
    $this->regions[$rname] = [
        "owner" => $name,
        "members" => [],
        "plan" => "dirt",
        "memberLimit" => $this->plans["dirt"]["members"],
        "level" => $level,
        "x1" => $x1,
        "z1" => $z1,
        "x2" => $x2,
        "z2" => $z2
    ];
    $this->saveRegions();
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aYour Region '$rname' created with plan §eDirt!");
    break;

        case "buy":
    if (!isset($args[1])){
        $sender->sendMessage("§cUsage: /rg buy <plan>");
    break;
}
    if ($this->economy === null){
        $sender->sendMessage("§cEconomyAPI not found. Please Install That Plugin First To Load RegionGuard Plugin.");
    break;
}
    $plan = strtolower($args[1]);
    if (!isset($this->plans[$plan])){
        $sender->sendMessage("§cUnknown plan. Please Re-Check The Plan Names.");
    break;
}
    $region = $this->getOwnedRegion($name);
    if ($region === null){
        $sender->sendMessage("§cYou don’t own a region. create a region first.");
    break;
}
    $currentPlan = $this->regions[$region]["plan"];
    if ($this->plans[$plan]["members"] <= $this->plans[$currentPlan]["members"]){
        $sender->sendMessage("§cYou cannot downgrade or buy the same plan. Try upgrade");
    break;
}
    $price = $this->plans[$plan]["price"];
    if ($this->economy->myMoney($sender) < $price){
        $sender->sendMessage("§cYou need §e$price §ccoins to upgrade.");
        break;
}
    $this->economy->reduceMoney($sender, $price);
    $newRadius = $this->plans[$plan]["radius"];
    $x1 = $this->regions[$region]["x1"];
    $x2 = $this->regions[$region]["x2"];
    $z1 = $this->regions[$region]["z1"];
    $z2 = $this->regions[$region]["z2"];
    $centerX = (int)(($x1 + $x2) / 2);
    $centerZ = (int)(($z1 + $z2) / 2);
    $newX1 = $centerX - $newRadius;
    $newZ1 = $centerZ - $newRadius;
    $newX2 = $centerX + $newRadius;
    $newZ2 = $centerZ + $newRadius;
    if ($this->overlaps(
        $newX1,
        $newZ1,
        $newX2,
        $newZ2,
        $this->regions[$region]["level"],
        $region )){
        $sender->sendMessage("§cUpgrade failed: new region overlaps another region.");
        $this->economy->addMoney($sender, $price);
        break;
}
    $this->regions[$region]["plan"] = $plan;
    $this->regions[$region]["memberLimit"] = $this->plans[$plan]["members"];
    $this->regions[$region]["x1"] = $newX1;
    $this->regions[$region]["z1"] = $newZ1;
    $this->regions[$region]["x2"] = $newX2;
    $this->regions[$region]["z2"] = $newZ2;
    $this->saveRegions();
    $sender->sendMessage("§aRegion upgraded to §e" . ucfirst($plan) . " §aplan for §e$price §acoins!" );
    break;

        case "addmember":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg addmember <player>");
        break;
}
    $region = $this->getOwnedRegion($name);
    if ($region === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou don’t have a region. First make a region by typing /rg create <name> ");
    break;
}
    $target = strtolower($args[1]);
    if ($target === $name){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cA Region Owner cannot be a member.");
    break;
}
    if ($this->getOwnedRegion($target) || $this->getMemberRegion($target)){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cThat Player already belongs to another region.");
    break;
}
    if (count($this->regions[$region]["members"]) >= $this->regions[$region]["memberLimit"]){
        $sender->sendMessage("§cMember slot is full.");
    break;
}
    $this->regions[$region]["members"][] = $target;
    $this->saveRegions();
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aPlayer §f{$args[1]} §ahas been added to your region.");
    $targetPlayer = $this->getServer()->getPlayer($args[1]);
    if ($targetPlayer !== null){
        $targetPlayer->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aYou have been added to the region §e$region §aby §f" . $sender->getName());
}
    break;
        case "kick":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg kick <player>");
    break;
}
    $region = $this->getOwnedRegion($name);
    if ($region === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou don’t have a region. Try make your region by typing /rg create <name> ");
        break;
}
    $target = strtolower($args[1]);
    if (!in_array($target, $this->regions[$region]["members"])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cThat player is not a member.");
        break;
}
    $this->regions[$region]["members"] = array_values(
        array_diff($this->regions[$region]["members"], [$target]));
    $this->saveRegions();
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cPlayer §f{$args[1]} §chas been kicked from the region.");
    $targetPlayer = $this->getServer()->getPlayer($args[1]);
        if ($targetPlayer !== null){
             $targetPlayer->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou have been kicked from the region §e$region §cby §f" . $sender->getName());
}
    break;
         case "setowner":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg setowner <player>");
    break;
}
    $region = $this->getOwnedRegion($name);
    if ($region === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou don’t own a region. First, Use /rg create <name> to make your region");
    break;
}
    $targetName = strtolower($args[1]);
    $targetPlayer = $this->getServer()->getPlayer($args[1]);
    if ($targetPlayer === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cThat player is not online.");
    break;
}
    if ($this->getOwnedRegion($targetName) || $this->getMemberRegion($targetName)){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cThat Player is already belongs to another region.");
        break;
}
    $this->pendingOwnerTransfers[$targetName] = [
        "region" => $region,
        "from" => $name ];
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aOwnership request sent to §f" . $targetPlayer->getName());
    $targetPlayer->sendMessage("§6§lRegion Ownership Request");
    $targetPlayer->sendMessage("§7Player §f" . $sender->getName() . " §7wants to transfer");
    $targetPlayer->sendMessage("§7ownership of region §e$region §7to you.");
    $targetPlayer->sendMessage("§aType §f/rg accept §ato accept");
    $targetPlayer->sendMessage("§cType §f/rg deny §cto deny");
    break;
case "accept":
    if (!isset($this->pendingOwnerTransfers[$name])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou have no pending ownership requests.");
    break;
}
    $data = $this->pendingOwnerTransfers[$name];
    $region = $data["region"];
    $oldOwner = $data["from"];
    $this->regions[$region]["members"][] = $oldOwner;
    $this->regions[$region]["owner"] = $name;
    $this->saveRegions();
    unset($this->pendingOwnerTransfers[$name]);
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aYou are now the owner of region §e$region");
    $oldOwnerPlayer = $this->getServer()->getPlayer($oldOwner);
    if ($oldOwnerPlayer !== null){
        $oldOwnerPlayer->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aOwnership of region §e$region §ahas been accepted.");
}
    break;
case "deny":
    if (!isset($this->pendingOwnerTransfers[$name])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou have no pending ownership requests.");
    break;
}
    $data = $this->pendingOwnerTransfers[$name];
    $region = $data["region"];
    $oldOwner = $data["from"];
    unset($this->pendingOwnerTransfers[$name]);
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou denied ownership of region §e$region");
    $oldOwnerPlayer = $this->getServer()->getPlayer($oldOwner);
    if ($oldOwnerPlayer !== null){
        $oldOwnerPlayer->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cOwnership transfer for region §e$region §cwas denied.");
}
    break;
case "myinfo":
    $region = $this->getOwnedRegion($name);
    if ($region === null){
        $region = $this->getMemberRegion($name);
}
    if ($region === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou do not belong to any region.");
    break;
}
    $r = $this->regions[$region];
    $members = empty($r["members"])
        ? "§8None"
        : "§f" . implode("§7, §f", $r["members"]);
    $sender->sendMessage("§6§l[ §eYour Region Info §6§l] ");
    $sender->sendMessage("§7Name: §e$region");
    $sender->sendMessage("§7Owner: §f" . $r["owner"]);
    $sender->sendMessage("§7Plan: §f" . ucfirst($r["plan"]));
    $sender->sendMessage("§7Members (§f" . count($r["members"]) . "/" . $r["memberLimit"] . "§7):");
    $sender->sendMessage("§7" . $members);
    break;
        case "list":
            $page = isset($args[1]) ? max(1, intval($args[1])) : 1;
            $perPage = 5;
            $allRegions = array_keys($this->regions);
            $totalRegions = count($allRegions);
            $totalPages = max(1, ceil($totalRegions / $perPage));
            if ($page > $totalPages) $page = $totalPages;

            if ($totalRegions === 0){
                $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cNo regions found.");
            break;
}
           $sender->sendMessage("§6§l[ §eRegions §6§l(Page $page/$totalPages) ] ");
            $start = ($page - 1) * $perPage;
            $end = min($start + $perPage, $totalRegions);
            for ($i = $start; $i < $end; $i++){
                $r = $allRegions[$i];
                $owner = $this->regions[$r]["owner"] ?? "Unknown";
                $sender->sendMessage("§e$r §7- Owner: §f$owner");
}
            $sender->sendMessage("§6RegionGuard powered by VeoZax");
            break;
        case "seeinfo":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg seeinfo <region>");
    break;
}
    $region = strtolower($args[1]);
    if (!isset($this->regions[$region])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cRegion not found.");
    break;
}
    $d = $this->regions[$region];
    $members = empty($d["members"])
        ? "§8None"
        : "§f" . implode("§7, §f", $d["members"]);
    $blocks = ($d["x2"] - $d["x1"] + 1) * ($d["z2"] - $d["z1"] + 1);
    $sender->sendMessage(
        "§6§l[ §eRegion Info §6§l]\n" .
        "§7Name: §e$region\n" .
        "§7Owner: §f" . $d["owner"] . "\n" .
        "§7Plan: §f" . ucfirst($d["plan"]) . "\n" .
        "§7Members (§f" . count($d["members"]) . "/" . $d["memberLimit"] . "§7):\n" .
        "§7" . $members . "\n" .
        "§7Blocks: §f" . $blocks . "\n" .
        "§6RegionGuard powered by VeoZax" );
break;
case "remove":
    if ($name !== "veozax"){ // Who Ever Uses This Plugin, Replace My Name With Your Actual Name.
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou do not have permission to use this command.");
    break;
}
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg remove <region>");
    break;
}
    $rname = strtolower($args[1]);
    if (!isset($this->regions[$rname])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cRegion not found.");
    break;
}
    $ownerName = $this->regions[$rname]["owner"];
    $owner = $this->getServer()->getPlayerExact($ownerName);
    if ($owner !== null){
        $owner->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYour region §e$rname §chas been removed by VeoZax.");
}
    foreach ($this->playerRegions as $p => $region){
        if ($region === $rname){
            $this->playerRegions[$p] = null;
}
}
    unset($this->regions[$rname]);
    $this->saveRegions();
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aRegion §e$rname §ahas been forcefully removed.");
    break;
case "delete":
    if (!isset($args[1])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUsage: /rg delete <region>");
    break;
}
    $rname = strtolower($args[1]);
    if (!isset($this->regions[$rname])){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cRegion not found.");
        break;
}
    if ($this->regions[$rname]["owner"] !== $name){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou are not the owner of this region.");
        break;
}
    unset($this->regions[$rname]);
    $this->saveRegions();
    foreach ($this->playerRegions as $p => $region){
        if ($region === $rname){
        $this->playerRegions[$p] = null;
}
}
$sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aRegion '$rname' has been deleted.");
    break;
case "leave":
    $region = $this->getMemberRegion($name);
    if ($region === null){
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou are not a member of any region.");
    break;
}
    $this->regions[$region]["members"] = array_values(
        array_diff($this->regions[$region]["members"], [$name]));
    $this->saveRegions();
    if (($this->playerRegions[$name] ?? null) === $region){
        $this->playerRegions[$name] = null;
}
    $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §aYou have left the region '$region'.");
    break;
        case "plans":
            $sender->sendMessage("§6§l[ §eRegion Plans §6§l] ");
            foreach ($this->plans as $p => $info){
            $sender->sendMessage(
                "§e" . ucfirst($p) . " §7- Radius: §f" . $info["radius"] .
                " §7Members: §f" . $info["members"] .
                " §7Price: §f" . $info["price"] . " coins"); }
        $sender->sendMessage("§6§lRegionGuard powered by VeoZax");
    break;
        default:
        $sender->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cUnknown subcommand. §eUse /rg for help.");
        break;
}
    return true;
}
    public function canEdit($player, $x, $z, $level){
        $name = strtolower($player->getName());
        foreach ($this->regions as $r){
            if ($r["level"] !== $level) continue;
            if ($x >= $r["x1"] && $x <= $r["x2"] && $z >= $r["z1"] && $z <= $r["z2"]){
                return ($r["owner"] === $name || in_array($name, $r["members"]));
}
}
        return true;
}
    public function onPlace(BlockPlaceEvent $event){
    $p = $event->getPlayer();
    $b = $event->getBlock();
    if(!$this->canEdit($p, $b->getX(), $b->getZ(), $b->getLevel()->getFolderName())){
        $event->setCancelled(true);
        $p->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou cannot place blocks inside another player's region.");
    }
    if(in_array($b->getId(), $this->blockedBlocks) && !$this->canEdit($p, $b->getX(), $b->getZ(), $b->getLevel()->getFolderName())){
        $event->setCancelled(true);
        $p->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou cannot place anything inside of another player's region!");
}
}
    private function overlaps($x1, $z1, $x2, $z2, $level, $ignoreRegion = null){
    foreach ($this->regions as $name => $r){
        if ($ignoreRegion !== null && $name === $ignoreRegion){
            continue;
}
        if ($r["level"] !== $level) continue;
        if (
            $x1 <= $r["x2"] &&
            $x2 >= $r["x1"] &&
            $z1 <= $r["z2"] &&
            $z2 >= $r["z1"]){
            return true;
}
}
    return false;
}
private function getRegionAt($x, $z, $level){
    foreach ($this->regions as $name => $r){
        if ($r["level"] !== $level) continue;
        if ($x >= $r["x1"] && $x <= $r["x2"] && $z >= $r["z1"] && $z <= $r["z2"]){
            return $name;
}
}
    return null;
}
public function onMove(PlayerMoveEvent $event){
    $player = $event->getPlayer();
    $name = strtolower($player->getName());
    $level = $player->getLevel()->getFolderName();
    $x = (int)$player->getX();
    $z = (int)$player->getZ();
    $currentRegion = $this->getRegionAt($x, $z, $level);
    $previousRegion = $this->playerRegions[$name] ?? null;
    if ($currentRegion !== $previousRegion){
        if ($currentRegion !== null){
            $owner = $this->regions[$currentRegion]["owner"];
            $player->sendPopup("§l§8[§6Region§aGuard§l§8]§r §eThis Region is Protected by §b$owner");
} else if ($previousRegion !== null){
        $player->sendPopup("§l§8[§7Region§fGuard§l§8]§r §7This area is unclaimed");
}
    $this->playerRegions[$name] = $currentRegion;
}
}
public function onEntityDamage(EntityDamageEvent $event){
    $victim = $event->getEntity();
    if(!($victim instanceof Player)) return;
    if($event instanceof EntityDamageByEntityEvent){
        $damager = $event->getDamager();
        if(!($damager instanceof Player)) return;
        $level = $victim->getLevel()->getFolderName();
        $x = (int)$victim->getX();
        $z = (int)$victim->getZ();
        $region = $this->getRegionAt($x, $z, $level);
        if($region === null) return;
        $attackerName = strtolower($damager->getName());
        $victimName = strtolower($victim->getName());
        $r = $this->regions[$region];
        if($r["owner"] !== $attackerName && !in_array($attackerName, $r["members"])){
            $event->setCancelled(true);
            $damager->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou cannot attack them while they are in their region!");
}
}
}
public function onPlayerInteract(PlayerInteractEvent $event){
    $block = $event->getBlock();
    if($block === null) return;
    $p = $event->getPlayer();
    $item = $event->getItem();
    $id = $item->getId();
    if(!$this->canEdit(
        $p,
        $block->getX(),
        $block->getZ(),
        $block->getLevel()->getFolderName())){
        if($id === 259 || in_array($id, $this->blockedTools) || isset($this->blockedItems[$id])){
            $event->setCancelled(true);
            $p->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou cannot use do this in another player's region.");
}       else{
        $event->setCancelled(true);
}
}
}
    public function onBreak(BlockBreakEvent $event){
    $p = $event->getPlayer();
    $b = $event->getBlock();

    if(!$this->canEdit($p, $b->getX(), $b->getZ(), $b->getLevel()->getFolderName())){
        $event->setCancelled(true);
        $p->sendMessage("§l§8[§6Region§aGuard§l§8]§r §cYou cannot break blocks inside another player's region.");
}
}
}
