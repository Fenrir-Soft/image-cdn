<?php

namespace Fenrir\ImageCdn\Domains\Image;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;

class ImageRepository
{
    private string $table = "image";
    public function __construct(
        private Connection $connection
    ) {
        $sm = $this->connection->createSchemaManager();
        if (!$sm->tableExists($this->table)) {
            $schema = $sm->introspectSchema();
            $table = $schema->createTable($this->table);

            $table->addColumn('id', Types::GUID);
            $table->setPrimaryKey(['id']);

            $table->addColumn('url', Types::STRING, ['length' => 500]);
            $table->addColumn('bucket', Types::STRING);
            $table->addColumn('hash', Types::STRING);
            $table->addColumn('width', Types::BIGINT);
            $table->addColumn('height', Types::BIGINT);
            $table->addColumn('size', Types::BIGINT);
            $table->addColumn('type', Types::STRING);
            $table->addColumn('caption', Types::STRING);

            $table->addIndex(['url']);
            $table->addIndex(['bucket']);
            $table->addIndex(['hash']);
            $table->addIndex(['width']);
            $table->addIndex(['height']);
            $table->addIndex(['type']);

            $sm->migrateSchema($schema);
        }
    }

    public function getById(string $id): ?ImageModel
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->andWhere('id = :id')
            ->setParameter('id', $id, ParameterType::STRING)
            ->executeQuery();

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }
        return ImageModel::fromArray($row);
    }

    public function deleteById(string $id): void
    {
        $this->connection->createQueryBuilder()
            ->delete($this->table)
            ->andWhere('id = :id')
            ->setParameter('id', $id, ParameterType::STRING)
            ->executeQuery();
    }

    public function getByUrl(string $url, string $bucket, string $hash)
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->andWhere('url = :url')
            ->andWhere('bucket = :bucket')
            ->andWhere('hash = :hash')
            ->setParameter('url', $url, ParameterType::STRING)
            ->setParameter('bucket', $bucket, ParameterType::STRING)
            ->setParameter('hash', $hash, ParameterType::STRING)
            ->executeQuery();

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }
        return ImageModel::fromArray($row);
    }

    public function save(ImageModel $record)
    {
        $exists = $this->getById($record->id);
        if (!$exists) {
            $exists = $this->getByUrl($record->url, $record->bucket, $record->hash);
        }
        if ($exists) {
            $record->id = $exists->id;
            return $this->update($record);
        }
        return $this->insert($record);
    }

    public function insert(ImageModel $record)
    {
        $this->connection->createQueryBuilder()
            ->insert($this->table)
            ->values([
                'id' => ':id',
                'bucket' => ':bucket',
                'hash' => ':hash',
                'caption' => ':caption',
                'url' => ':url',
                'width' => ':width',
                'height' => ':height',
                'size' => ':size',
                'type' => ':type'
            ])
            ->setParameters([
                'id' => $record->id,
                'bucket' => $record->bucket,
                'hash' => $record->hash,
                'caption' => $record->caption,
                'url' => $record->url,
                'width' => $record->width,
                'height' => $record->height,
                'size' => $record->size,
                'type' => $record->type,
            ])
            ->executeQuery();
    }
    public function update(ImageModel $record)
    {
        $this->connection->createQueryBuilder()
            ->update($this->table)
            ->andWhere('id = :id')
            ->set('bucket', ':bucket')
            ->set('hash', ':hash')
            ->set('url', ':url')
            ->set('caption', ':caption')
            ->set('width', ':width')
            ->set('height', ':height')
            ->set('size', ':size')
            ->set('type', ':type')
            ->setParameters([
                'id' => $record->id,
                'bucket' => $record->bucket,
                'hash' => $record->hash,
                'url' => $record->url,
                'caption' => $record->caption,
                'width' => $record->width,
                'height' => $record->height,
                'size' => $record->size,
                'type' => $record->type,
            ])
            ->executeQuery();
    }
}
