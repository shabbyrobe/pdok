<?php
namespace PDOK;

final class Functions
{
    private function __construct() {}

    public static function ensurePDO($pdo)
    {
        if (!$pdo instanceof \PDO && !$pdo instanceof \PDOK\Connector) {
            $type = ($type = gettype($pdo)) == 'object' ? get_class($pdo) : $type;
            throw new \InvalidArgumentException("Must be an instance of PDO or PDOK\Connector, found ".$type);
        }
        return $pdo;
    }

    public static function ensureStatement($stmt)
    {
        if (!$stmt instanceof \PDOStatement && !$stmt instanceof \PDOK\StatementWrapper) {
            $type = ($type = gettype($stmt)) == 'object' ? get_class($stmt) : $type;
            throw new \InvalidArgumentException("Must be an instance of PDOStatement or PDOK\StatementWrapper, found ".$type);
        }
        return $stmt;
    }
}
