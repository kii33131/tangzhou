<?php
namespace app\service;

use think\Collection;

class IndustryCategoryService
{

	/**
	 * 树形结构
	 *
	 * @time at 2018年11月13日
	 * @param $menu
	 * @return Collection
	 */
	public function tree(Collection $menus, int $pid = 0)
	{
		$collection = new Collection();

		$menus->each(function ($item, $key) use ($pid, $menus, $collection){
				if ($item->pid == $pid) {
					$collection[$key] = $item;
					$collection[$key][$item->id] = $this->tree($menus, $item->id);
				}
		});

		return $collection;
	}

	/**
	 * 顺序结构
	 *
	 * @time at 2018年11月13日
	 * @param $menu
	 * @return Collection
	 */
	public function sort(Collection $industrys, int $pid = 0, int $level = 0)
	{
		$collection = [];
		foreach ($industrys as $industry) {
			if ($industry->pid == $pid) {
                $industry->level = $level;
				$collection[] = $industry;
				$collection  = array_merge($collection, $this->sort($industrys, $industry->id, $level+1));
			}
		}
		return $collection;
	}
}