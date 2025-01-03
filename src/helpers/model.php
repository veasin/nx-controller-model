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
		'page'=>['int', 'query', 'null'=>1, 'digit'=>['>'=>0]],
		'max'=>['int', 'query', 'null'=>10, 'digit'=>['>'=>0]],
		'sort'=>['str', 'query', 'null'=>'id'],
		'desc'=>['int', 'query', 'null'=>1],
	];
	protected array $output=[];
	abstract protected function multiple($conditions=[]):?multiple;
	abstract protected function single($conditions=[]):?single;
	public function list():void{
		$multiple=$this->multiple(empty($this->multiple) ?[] :$this->filter($this->multiple, ['error'=>404]));
		if(null === $multiple) $this->throw(404);
		$output=$multiple->list($this->filter($this->list), ["output"=>$this->output,...$this->filter($this->options)]);
		$next=func_get_arg(0);
		$this->out($next($output, $multiple) ?? $output);
	}
	public function add():void{
		$multiple=$this->multiple(empty($this->multiple) ?[] :$this->filter($this->multiple, ['error'=>404]));
		if(null === $multiple) $this->throw(404);
		if(!method_exists($multiple, 'create')) $this->throw(501);
		else{
			$data = $this->filter($this->create, ['error' => 400]);
			$next = func_get_arg(0);
			$this->throw($multiple->create($next($data, $multiple) ?? $data)->save() ? 201 : 500);
		}
	}
	public function get(){
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(404);
		$output=$single->output($this->output);
		$next=func_get_arg(0);
		return $this->out($next($output, $single) ?? $output);
	}
	public function update():void{
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(404);
		$data=$this->filter($this->update, ['error'=>400]);
		$next=func_get_arg(0);
		$this->throw($single->update($next($data, $single) ?? $data)->save() ?204 :500);
	}
	public function delete():void{
		$single=$this->single(empty($this->single) ?[] :$this->filter($this->single, ['error'=>404]));
		if(null === $single) $this->throw(204);
		$this->throw($single->delete() ?204 :500);
	}
}