<?php
/*
 * Base Model
 * Common functionality for all models
 */
class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;

    public function __construct(){
        $this->db = new Database;
    }

    public function find($id){
        $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findAll(){
        $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function findWhere($conditions, $operator = 'AND'){
        $whereClause = [];
        foreach($conditions as $field => $value){
            $whereClause[] = "{$field} = :{$field}";
        }
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(" {$operator} ", $whereClause);
        $this->db->query($query);
        
        foreach($conditions as $field => $value){
            $this->db->bind(":{$field}", $value);
        }
        
        return $this->db->resultSet();
    }

    public function create($data){
        $data = $this->filterFillable($data);
        
        if($this->timestamps){
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        $this->db->query($query);
        
        foreach($data as $field => $value){
            $this->db->bind(":{$field}", $value);
        }
        
        if($this->db->execute()){
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data){
        $data = $this->filterFillable($data);
        
        if($this->timestamps){
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setClause = [];
        foreach($data as $field => $value){
            $setClause[] = "{$field} = :{$field}";
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = :id";
        $this->db->query($query);
        
        foreach($data as $field => $value){
            $this->db->bind(":{$field}", $value);
        }
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    public function delete($id){
        $this->db->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function count($conditions = []){
        if(empty($conditions)){
            $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        } else {
            $whereClause = [];
            foreach($conditions as $field => $value){
                $whereClause[] = "{$field} = :{$field}";
            }
            
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
            $this->db->query($query);
            
            foreach($conditions as $field => $value){
                $this->db->bind(":{$field}", $value);
            }
        }
        
        $result = $this->db->single();
        return $result->count;
    }

    public function paginate($page = 1, $perPage = 10, $conditions = []){
        $offset = ($page - 1) * $perPage;
        
        if(empty($conditions)){
            $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        } else {
            $whereClause = [];
            foreach($conditions as $field => $value){
                $whereClause[] = "{$field} = :{$field}";
            }
            
            $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause) . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $this->db->query($query);
            
            foreach($conditions as $field => $value){
                $this->db->bind(":{$field}", $value);
            }
        }
        
        $this->db->bind(':limit', $perPage);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultSet();
    }

    protected function filterFillable($data){
        if(empty($this->fillable)){
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideFields($data){
        if(empty($this->hidden)){
            return $data;
        }
        
        if(is_array($data)){
            foreach($data as &$item){
                if(is_object($item)){
                    foreach($this->hidden as $field){
                        unset($item->$field);
                    }
                }
            }
        } elseif(is_object($data)){
            foreach($this->hidden as $field){
                unset($data->$field);
            }
        }
        
        return $data;
    }

    public function beginTransaction(){
        $this->db->beginTransaction();
    }

    public function commit(){
        $this->db->commit();
    }

    public function rollback(){
        $this->db->rollback();
    }
}
?>