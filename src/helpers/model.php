<?php
namespace nx\helpers\controller;

use nx\parts\callApp;
use nx\helpers\model\multiple;
use nx\helpers\model\single;
use nx\parts\model\cache;

abstract class model{
	use callApp, cache;

	protected array $create=[];
	protected array $update=[];
	protected array $single=[];
	protected array $multiple=[];
	protected array $list=[];
	protected array $options=[
		'page'=>['int', 'query', 'digit'=>['>'=>0], 'null'=>1],
		'max'=>['int', 'query', 'digit'=>['>'=>0], 'null'=>10],
		'sort'=>['str', 'query', 'null'=>'id'],
		'desc'=>['int', 'query', 'null'=>1],
	];
	abstract protected function multiple($conditions=[]):?multiple;
	abstract protected function single($conditions=[]):?single;
	public function list():void{
		$multiple=$this->multiple(empty($this->multiple) ?[] :$this->filter($this->multiple, ['error'=>404]));
		if(null === $multiple) $this->throw(404);
		$output=$multiple->list($this->filter($this->list), $this->filter($this->options));
		$next=func_get_arg(0);
		$result=$next($output, $multiple);
		if(null !== $result) $output=$result;
		$this->out($output);
	}
	public function add():void{
		$multiple=$this->multiple(empty($this->multiple) ?[] :$this->filter($this->multiple, ['error'=>404]));
		if(null === $multiple) $this->throw(404);
		if(!method_exists($multiple, 'create')) $this->throw(501);
		else{
			$data = $this->filter($this->create, ['error' => 400]);
			$next = func_get_arg(0);
			$result = $next($data, $multiple);
			if(null !== $result) $data = $result;
			$this->throw($multiple->create($data)->save() ? 201 : 500);
		}
	}
	public function get(){
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(404);
		$output=$single->output();
		$next=func_get_arg(0);
		$result=$next($output, $single);
		if(null !== $result) $output=$result;
		return $this->out($output);
	}
	public function update():void{
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(404);
		$data=$this->filter($this->update, ['error'=>400]);
		$next=func_get_arg(0);
		$result=$next($data, $single);
		if(null !== $result) $data=$result;
		$this->throw($single->update($data)->save() ?204 :500);
	}
	public function delete():void{
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(204);
		$this->throw($single->delete() ?204 :500);
	}
}