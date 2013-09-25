<?php

namespace Application\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodHydrator;

/**
 * @pattern-notes
 *
 * - This is a DataMapper
 *
 * - This DataMapper internally builds on the Table Gateway pattern,
 *   delivered by Zend\Db\TableGateway
 */
class DataMapper
{
    protected $dbAdapter;
    protected $playlistTable;

    public function __construct(DbAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;

        $playlistPrototype = new Playlist();
        $playlistPrototype->setTracks($this->lazyLoadTracksClosure());

        $this->playlistTable = new TableGateway(
            'playlist',
            $this->dbAdapter,
            null,
            // Zend\Stdlib\Hydrator\ClassMethods will do
            // $obj->setFirstName($a['first_name'])
            // for each property on iteration
            new HydratingResultSet(new ClassMethodHydrator(), $playlistPrototype)
        );

    }

    public function findPlaylists()
    {
        $playlists = $this->playlistTable->select();
        return $playlists;
    }
    
    public function findPlaylistBy(array $info)
    {
        $playlists = $this->playlistTable->select($info);
        return $playlists;
    }

    /**
     * @pattern-notes
     *
     * - This implements Lazy Loading (PoEAA)
     *
     * @param $playlistId
     * @return callable
     */
    protected function lazyLoadTracksClosure()
    {
        $dataMapper = $this; // php 5.3 hack, must be renamed

        $trackTable = new TableGateway('track', $dataMapper->dbAdapter, null, new HydratingResultSet(new ClassMethodHydrator(), new Track));
        $artistTable = new TableGateway('artist', $dataMapper->dbAdapter, null, new HydratingResultSet(new ClassMethodHydrator(), new Artist));
        $albumTable = new TableGateway('album', $dataMapper->dbAdapter, null, new HydratingResultSet(new ClassMethodHydrator(), new Album));

        return function ($playlistId) use ($dataMapper, $trackTable, $artistTable, $albumTable) {
            static $albumCache = array(), $artistCache = array();

            $tracks = iterator_to_array($trackTable->select(function (\Zend\Db\Sql\Select $select) {
                $select->join('playlist_track', 'playlist_track.track_id = track.id', array());
            }));

            /** @var $row \ArrayObject */
            foreach ($tracks as $id => $track) {

                if (!array_key_exists($track->getArtistId(), $artistCache)) {
                    $artists = $artistTable->select(array('id' => $track->getArtistId()));
                    $artist = $artists->current();
                    $artistCache[$artist->getId()] = $artist;
                }


                if (!array_key_exists($track->getAlbumId(), $albumCache)) {
                    $albums = $albumTable->select(array('id' => $track->getAlbumId()));
                    $album = $albums->current();
                    $albumCache[$album->getId()] = $album;
                }

                $track->setArtist($artistCache[$track->getArtistId()]);
                $track->setAlbum($albumCache[$track->getAlbumId()]);

            }
            return $tracks;
        };
    }
    
}